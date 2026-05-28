<?php

namespace App\Services\Admin\Catalog;

use App\Enums\FileType;
use App\Models\Product\MasterProduct;
use App\Notifications\Catalog\MasterProductApprovedNotification;
use App\Traits\Paginatable;
use App\Traits\ResolvesDisplayName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MasterProductService
{
    use Paginatable, ResolvesDisplayName;

    public function listProducts(Request $request)
    {
        return MasterProduct::query()
            ->withListRelations()
            ->approvalStatus($request->query('approval_status'))
            ->inCategory($request->query('category_id'))
            ->unitType($request->query('unit_type'))
            ->active($request->has('is_active') ? $request->boolean('is_active') : null)
            ->searchName($request->query('search'))
            ->newest()
            ->cursorPaginate($this->getPerPageLimit());
    }

    public function createProduct(array $data): MasterProduct
    {
        return DB::transaction(function () use ($data) {
            $product = MasterProduct::create([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'unit_type' => $data['unit_type'],
                'is_active' => $data['is_active'] ?? true,
            ]);

            if (! empty($data['description'])) {
                $product->description()->create([
                    'description' => $data['description'],
                ]);
            }

            if (! empty($data['brand_id']) || ! empty($data['sku_barcode'])) {
                $product->retailDetail()->create([
                    'brand_id' => $data['brand_id'] ?? null,
                    'sku_barcode' => $data['sku_barcode'] ?? null,
                ]);
            }

            $image = $data['image'] ?? null;
            if ($image) {
                $path = $image->store('master-products', 'public');
                $product->media()->create([
                    'file_path' => $path,
                    'file_type' => FileType::Image,
                    'is_primary' => true,
                ]);
            }

            $images = $data['images'] ?? [];
            if (! empty($images)) {
                foreach ($images as $img) {
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

    public function updateProduct(MasterProduct $product, array $data): MasterProduct
    {
        return DB::transaction(function () use ($product, $data) {
            $product->update(collect($data)->only(['category_id', 'name', 'unit_type', 'is_active'])->toArray());

            if (array_key_exists('description', $data)) {
                if (! empty($data['description'])) {
                    $product->description()->updateOrCreate([], ['description' => $data['description']]);
                } else {
                    $product->description()->delete();
                }
            }

            if (array_key_exists('brand_id', $data) || array_key_exists('sku_barcode', $data)) {
                if (! empty($data['brand_id'])) {
                    $product->retailDetail()->updateOrCreate([], [
                        'brand_id' => $data['brand_id'],
                        'sku_barcode' => $data['sku_barcode'] ?? null,
                    ]);
                } else {
                    $product->retailDetail()->delete();
                }
            }

            $image = $data['image'] ?? null;
            if ($image) {
                $path = $image->store('master-products', 'public');

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

            $images = $data['images'] ?? null;
            if ($images !== null) {
                // Delete old non-primary images
                $oldImages = $product->media()->where('is_primary', false)->get();
                foreach ($oldImages as $oldImg) {
                    Storage::disk('public')->delete($oldImg->file_path);
                    $oldImg->delete();
                }

                // Create new ones
                foreach ($images as $img) {
                    $path = $img->store('master-products', 'public');
                    $product->media()->create([
                        'file_path' => $path,
                        'file_type' => FileType::Image,
                        'is_primary' => false,
                    ]);
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
