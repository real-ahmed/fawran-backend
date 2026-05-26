<?php

namespace App\Services\Admin;

use App\Models\Catalog\Brand;
use App\Models\Vendor\Vendor;
use App\Notifications\CatalogItemApproved;
use App\Traits\Paginatable;

class BrandService
{
    use Paginatable;

    public function listBrands(?string $search = null, ?string $approvalStatus = null)
    {
        return Brand::query()
            ->when($search, function ($query, $search) {
                $query->where('name->en', 'like', "%{$search}%")
                    ->orWhere('name->ar', 'like', "%{$search}%");
            })
            ->when($approvalStatus, function ($query, $status) {
                $query->whereHas('vendorSubmission', fn ($q) => $q->where('status', $status));
            })
            ->with('vendorSubmission.vendor')
            ->paginate($this->getPerPageLimit());
    }

    public function createBrand(array $data): Brand
    {
        return Brand::create($data);
    }

    public function updateBrand(Brand $brand, array $data): Brand
    {
        $brand->update($data);

        return $brand;
    }

    public function deleteBrand(Brand $brand): void
    {
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
