<?php

namespace App\Services\Vendor\Catalog;

use App\DTOs\Vendor\Catalog\Brand\BrandSubmissionDTO;
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
