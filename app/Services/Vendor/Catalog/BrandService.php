<?php

namespace App\Services\Vendor\Catalog;

use App\Enums\AdminPermission;
use App\Events\Catalog\BrandSubmitted;
use App\Models\Catalog\Brand;
use App\Models\Vendor\Vendor;
use App\Notifications\Admin\CatalogSubmissionReceivedNotification;
use App\Services\AdminNotificationService;

class BrandService
{
    public function __construct(
        private readonly CatalogSubmissionAdminResolver $adminResolver,
        private readonly AdminNotificationService $notificationService
    ) {}

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
        $admins = $this->adminResolver->forVendor($vendor);

        foreach ($admins as $admin) {
            event(new BrandSubmitted($brand, $admin));
        }

        $this->notificationService->notifyAdminsWithPermission(
            AdminPermission::APPROVE_BRANDS->value,
            new CatalogSubmissionReceivedNotification($brand->name['en'] ?? 'Brand', 'Brand'),
            $admins
        );
    }
}
