<?php

namespace App\Services\Admin;

use App\Enums\FileType;
use App\Models\Catalog\Brand;
use App\Models\Vendor\Vendor;
use App\Notifications\CatalogItemApproved;
use App\Traits\Paginatable;
use Illuminate\Support\Facades\Storage;

class BrandService
{
    use Paginatable;

    public function __construct(protected CatalogRealtimeNotifier $catalogRealtimeNotifier) {}

    public function listBrands(?string $search = null, ?string $approvalStatus = null, ?bool $isActive = null)
    {
        return Brand::query()
            ->withListRelations()
            ->searchName($search)
            ->approvalStatus($approvalStatus)
            ->active($isActive)
            ->paginate($this->getPerPageLimit())
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

    public function proposeBrand(array $data, Vendor $vendor): Brand
    {
        $data['is_active'] = false; // Force inactive until approved
        $brand = Brand::create($data);

        $brand->vendorSubmission()->create([
            'vendor_id' => $vendor->id,
            'status' => 'pending',
        ]);

        $this->catalogRealtimeNotifier->notifySubmission('Brand', $brand->id, $brand->name, $vendor);

        return $brand;
    }

    public function approveBrand(Brand $brand): void
    {
        $brand->update(['is_active' => true]);

        if ($submission = $brand->vendorSubmission) {
            $vendor = $submission->vendor;
            if ($vendor && $vendor->owner) {
                // Determine name string based on locales, defaulting to 'en'
                $brandName = is_array($brand->name) ? ($brand->name['en'] ?? current($brand->name)) : 'Unknown';
                $vendor->owner->notify(new CatalogItemApproved('Brand', $brandName));
            }
            $submission->delete();
        }
    }

    public function rejectBrand(Brand $brand): void
    {
        if ($submission = $brand->vendorSubmission) {
            $submission->update(['status' => 'rejected']);
        }
    }
}
