<?php

namespace App\Services\Vendor\Catalog;

use App\Events\Catalog\MasterProductSubmitted;
use App\Models\Product\MasterProduct;
use App\Models\Vendor\Vendor;
use Illuminate\Support\Facades\DB;

class MasterProductService
{
    public function __construct(private readonly CatalogSubmissionAdminResolver $adminResolver) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function submit(array $data, Vendor $vendor): MasterProduct
    {
        return DB::transaction(function () use ($data, $vendor): MasterProduct {
            $product = MasterProduct::create([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'unit_type' => $data['unit_type'],
                'is_active' => false,
            ]);

            if (! empty($data['description'])) {
                $product->description()->create([
                    'description' => $data['description'],
                ]);
            }

            if (! empty($data['brand_id'])) {
                $product->retailDetail()->create([
                    'brand_id' => $data['brand_id'],
                    'sku_barcode' => $data['sku_barcode'] ?? null,
                ]);
            }

            $product->vendorSubmission()->create([
                'vendor_id' => $vendor->id,
                'status' => 'pending',
            ]);

            $this->broadcastSubmission($product, $vendor);

            return $product->load(['category', 'description', 'retailDetail']);
        });
    }

    private function broadcastSubmission(MasterProduct $product, Vendor $vendor): void
    {
        foreach ($this->adminResolver->forVendor($vendor) as $admin) {
            event(new MasterProductSubmitted($product, $admin));
        }
    }
}
