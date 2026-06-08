<?php

namespace App\Services\Vendor\Catalog;

use App\DTOs\Vendor\Catalog\MasterProduct\MasterProductSubmissionDTO;
use App\Models\Product\MasterProduct;
use App\Models\Vendor\Vendor;
use Illuminate\Support\Facades\DB;

class MasterProductService
{
    public function submit(MasterProductSubmissionDTO $dto, Vendor $vendor): MasterProduct
    {
        return DB::transaction(function () use ($dto, $vendor): MasterProduct {
            $product = MasterProduct::create([
                'category_id' => $dto->category_id,
                'name' => $dto->name,
                'unit_type' => $dto->unit_type,
                'is_active' => false,
            ]);

            if (! empty($dto->description)) {
                $product->description()->create([
                    'description' => $dto->description,
                ]);
            }

            if (! empty($dto->brand_id)) {
                $product->retailDetail()->create([
                    'brand_id' => $dto->brand_id,
                    'sku_barcode' => $dto->sku_barcode,
                ]);
            }

            $product->vendorSubmission()->create([
                'vendor_id' => $vendor->id,
                'status' => 'pending',
            ]);

            return $product->load(['category', 'description', 'retailDetail']);
        });
    }
}
