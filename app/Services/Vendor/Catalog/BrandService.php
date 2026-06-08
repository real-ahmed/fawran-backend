<?php

namespace App\Services\Vendor\Catalog;

use App\DTOs\Vendor\Catalog\Brand\BrandSubmissionDTO;
use App\Models\Catalog\Brand;
use App\Models\Vendor\Vendor;

class BrandService
{
    public function submit(BrandSubmissionDTO $dto, Vendor $vendor): Brand
    {
        $brand = Brand::create([
            'name' => $dto->name,
            'is_active' => false,
        ]);

        $brand->vendorSubmission()->create([
            'vendor_id' => $vendor->id,
            'status' => 'pending',
        ]);

        return $brand;
    }
}
