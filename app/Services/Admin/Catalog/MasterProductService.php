<?php

namespace App\Services\Admin\Catalog;

use App\DTOs\Admin\Catalog\MasterProductDataDTO;
use App\DTOs\Admin\Catalog\MasterProductFilterDTO;
use App\Enums\FileType;
use App\Models\Product\MasterProduct;
use App\Notifications\Catalog\MasterProductApprovedNotification;
use App\Traits\Paginatable;
use App\Traits\ResolvesDisplayName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MasterProductService
{
    use Paginatable, ResolvesDisplayName;

    public function listProducts(MasterProductFilterDTO $filters)
    {
        return MasterProduct::query()
            ->withListRelations()
            ->approvalStatus($filters->approval_status)
            ->inCategory($filters->category_id)
            ->active($filters->is_active)
            ->searchName($filters->search)
            ->newest()
            ->cursorPaginate($this->getPerPageLimit());
    }

    public function createProduct(MasterProductDataDTO $dto): MasterProduct
    {
        return DB::transaction(function () use ($dto) {
            $product = MasterProduct::create([
                'category_id' => $dto->category_id,
                'name' => $dto->name,
                'unit_type' => $dto->unit_type,
                'is_active' => $dto->is_active ?? true,
            ]);

            if (! empty($dto->description)) {
                $product->description()->create([
                    'description' => $dto->description,
                ]);
            }

            if (! empty($dto->brand_id) || ! empty($dto->sku_barcode)) {
                $product->retailDetail()->create([
                    'brand_id' => $dto->brand_id ?? null,
                    'sku_barcode' => $dto->sku_barcode ?? null,
                ]);
            }

            if ($dto->image) {
                $path = $dto->image->store('master-products', 'public');
                $product->media()->create([
                    'file_path' => $path,
                    'file_type' => FileType::Image,
                    'is_primary' => true,
                ]);
            }

            if (! empty($dto->images)) {
                foreach ($dto->images as $img) {
                    $path = $img->store('master-products', 'public');
                    $product->media()->create([
                        'file_path' => $path,
                        'file_type' => FileType::Image,
                        'is_primary' => false,
                    ]);
                }
            }

            return $product->load(['category', 'description', 'retailDetail', 'media']);
        });
    }

    public function getProduct(MasterProduct $product): MasterProduct
    {
        return $product->load(['category', 'description', 'retailDetail']);
    }

    public function updateProduct(MasterProduct $product, MasterProductDataDTO $dto): MasterProduct
    {
        return DB::transaction(function () use ($product, $dto) {
            $updateData = [];
            if ($dto->category_id !== null) {
                $updateData['category_id'] = $dto->category_id;
            }
            if ($dto->name !== null) {
                $updateData['name'] = $dto->name;
            }
            if ($dto->unit_type !== null) {
                $updateData['unit_type'] = $dto->unit_type;
            }
            if ($dto->is_active !== null) {
                $updateData['is_active'] = $dto->is_active;
            }

            if (! empty($updateData)) {
                $product->update($updateData);
            }

            if ($dto->description !== null) {
                if (! empty($dto->description)) {
                    $product->description()->updateOrCreate([], ['description' => $dto->description]);
                } else {
                    $product->description()->delete();
                }
            }

            if ($dto->has_brand_id || $dto->has_sku_barcode) {
                if (! empty($dto->brand_id)) {
                    $product->retailDetail()->updateOrCreate([], [
                        'brand_id' => $dto->brand_id,
                        'sku_barcode' => $dto->sku_barcode ?? null,
                    ]);
                } else {
                    $product->retailDetail()->delete();
                }
            }

            if ($dto->image) {
                $path = $dto->image->store('master-products', 'public');

                $oldMedia = $product->media()->where('is_primary', true)->first();
                if ($oldMedia) {
                    Storage::disk('public')->delete($oldMedia->file_path);
                    $oldMedia->delete();
                }

                $product->media()->create([
                    'file_path' => $path,
                    'file_type' => FileType::Image,
                    'is_primary' => true,
                ]);
            }

            if ($dto->has_images) {
                // Delete old non-primary images
                $oldImages = $product->media()->where('is_primary', false)->get();
                foreach ($oldImages as $oldImg) {
                    Storage::disk('public')->delete($oldImg->file_path);
                    $oldImg->delete();
                }

                if (! empty($dto->images)) {
                    // Create new ones
                    foreach ($dto->images as $img) {
                        $path = $img->store('master-products', 'public');
                        $product->media()->create([
                            'file_path' => $path,
                            'file_type' => FileType::Image,
                            'is_primary' => false,
                        ]);
                    }
                }
            }

            return $product->refresh()->load(['category', 'description', 'retailDetail', 'media']);
        });
    }

    public function deleteProduct(MasterProduct $product): void
    {
        DB::transaction(function () use ($product) {
            $media = $product->media()->get();
            foreach ($media as $item) {
                Storage::disk('public')->delete($item->file_path);
                $item->delete();
            }
            $product->description()->delete();
            $product->retailDetail()->delete();
            $product->vendorSubmission()->delete();
            $product->delete();
        });
    }

    public function approveProduct(MasterProduct $product): void
    {
        $product->update(['is_active' => true]);
        $product->loadMissing('vendorSubmission.vendor.owner');

        $submission = $product->vendorSubmission;

        if (! $submission) {
            return;
        }

        if ($submission->vendor?->owner) {
            $submission->vendor->owner->notify(new MasterProductApprovedNotification($this->displayName($product->name)));
        }

        $submission->delete();
    }

    public function rejectProduct(MasterProduct $product, ?string $reason = null): void
    {
        $product->vendorSubmission()->update([
            'status' => 'rejected',
            'reason' => $reason,
        ]);
    }
}
