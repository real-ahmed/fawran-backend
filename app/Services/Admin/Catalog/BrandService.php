<?php

namespace App\Services\Admin\Catalog;

use App\Enums\FileType;
use App\Http\Requests\Admin\Brand\IndexBrandRequest;
use App\Models\Catalog\Brand;
use App\Notifications\Catalog\BrandApprovedNotification;
use App\Traits\Paginatable;
use App\Traits\ResolvesDisplayName;
use Illuminate\Support\Facades\Storage;

class BrandService
{
    use Paginatable, ResolvesDisplayName;

    public function listBrands(IndexBrandRequest $request)
    {
        return Brand::query()
            ->withListRelations()
            ->searchName($request->validated('search'))
            ->approvalStatus($request->validated('approval_status'))
            ->active($request->has('is_active') ? $request->boolean('is_active') : null)
            ->newest()
            ->cursorPaginate($this->getPerPageLimit())
            ->withQueryString();
    }

    public function createBrand(array $data): Brand
    {
        $image = $data['image'] ?? null;
        unset($data['image']);

        $brand = Brand::create($data);

        if ($image) {
            $path = $image->store('brands', 'public');
            $brand->media()->create([
                'file_path' => $path,
                'file_type' => FileType::Image,
                'is_primary' => true,
            ]);
        }

        return $brand->load('media');
    }

    public function updateBrand(Brand $brand, array $data): Brand
    {
        $image = $data['image'] ?? null;
        unset($data['image']);

        $brand->update($data);

        if ($image) {
            $path = $image->store('brands', 'public');

            $oldMedia = $brand->media()->where('is_primary', true)->first();
            if ($oldMedia) {
                Storage::disk('public')->delete($oldMedia->file_path);
                $oldMedia->delete();
            }

            $brand->media()->create([
                'file_path' => $path,
                'file_type' => FileType::Image,
                'is_primary' => true,
            ]);
        }

        return $brand->load('media');
    }

    public function deleteBrand(Brand $brand): void
    {
        $media = $brand->media()->get();
        foreach ($media as $item) {
            Storage::disk('public')->delete($item->file_path);
            $item->delete();
        }
        $brand->delete();
    }

    public function approveBrand(Brand $brand): void
    {
        $brand->update(['is_active' => true]);
        $brand->loadMissing('vendorSubmission.vendor.owner');

        $submission = $brand->vendorSubmission;

        if (! $submission) {
            return;
        }

        if ($submission->vendor?->owner) {
            $submission->vendor->owner->notify(new BrandApprovedNotification($this->displayName($brand->name)));
        }

        $submission->delete();
    }

    public function rejectBrand(Brand $brand): void
    {
        $brand->vendorSubmission()->update(['status' => 'rejected']);
    }
}
