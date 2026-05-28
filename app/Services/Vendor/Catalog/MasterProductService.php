<?php

namespace App\Services\Vendor\Catalog;

use App\Enums\AdminPermission;
use App\Events\Catalog\MasterProductSubmitted;
use App\Models\Product\MasterProduct;
use App\Models\Vendor\Vendor;
use App\Notifications\Admin\CatalogSubmissionReceivedNotification;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\DB;

class MasterProductService
{
    public function __construct(
        private readonly CatalogSubmissionAdminResolver $adminResolver,
        private readonly AdminNotificationService $notificationService
    ) {}

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
        $admins = $this->adminResolver->forVendor($vendor);

        foreach ($admins as $admin) {
            event(new MasterProductSubmitted($product, $admin));
        }

        $this->notificationService->notifyAdminsWithPermission(
            AdminPermission::APPROVE_MASTER_PRODUCTS->value,
            new CatalogSubmissionReceivedNotification($product->name['en'] ?? 'Product', 'Master Product'),
            $admins
        );
    }
}
