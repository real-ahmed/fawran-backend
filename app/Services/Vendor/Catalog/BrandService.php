<?php

namespace App\Services\Vendor\Catalog;

use App\Events\Catalog\BrandSubmitted;
use App\Models\Catalog\Brand;
use App\Models\Vendor\Vendor;

class BrandService
{
    public function __construct(private readonly CatalogSubmissionAdminResolver $adminResolver) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function submit(array $data, Vendor $vendor): Brand
    {
        $data['is_active'] = false;

        $brand = Brand::create($data);

        $brand->vendorSubmission()->create([
            'vendor_id' => $vendor->id,
            'status' => 'pending',
        ]);

        $this->broadcastSubmission($brand, $vendor);

        return $brand;
    }

    private function broadcastSubmission(Brand $brand, Vendor $vendor): void
    {
        foreach ($this->adminResolver->forVendor($vendor) as $admin) {
            event(new BrandSubmitted($brand, $admin));
        }
    }
}
