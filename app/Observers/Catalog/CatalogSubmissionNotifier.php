<?php

namespace App\Observers\Catalog;

use App\Enums\AdminPermission;
use App\Events\Catalog\BrandSubmitted;
use App\Events\Catalog\CategorySubmitted;
use App\Events\Catalog\MasterProductSubmitted;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Product\MasterProduct;
use App\Models\Vendor\Vendor;
use App\Notifications\Admin\CatalogSubmissionReceivedNotification;
use App\Services\AdminNotificationService;
use App\Services\Vendor\Catalog\CatalogSubmissionAdminResolver;

class CatalogSubmissionNotifier
{
    public function __construct(
        private readonly CatalogSubmissionAdminResolver $adminResolver,
        private readonly AdminNotificationService $notificationService
    ) {}

    public function notifyBrandSubmitted(Brand $brand, Vendor $vendor): void
    {
        $admins = $this->adminResolver->forVendor($vendor);

        foreach ($admins as $admin) {
            event(new BrandSubmitted($brand, $admin));
        }

        $this->notificationService->notifyAdminsWithPermission(
            AdminPermission::APPROVE_BRANDS->value,
            new CatalogSubmissionReceivedNotification($this->displayName($brand->name, 'Brand'), 'Brand'),
            $admins
        );
    }

    public function notifyCategorySubmitted(Category $category, Vendor $vendor): void
    {
        $admins = $this->adminResolver->forVendor($vendor);

        foreach ($admins as $admin) {
            event(new CategorySubmitted($category, $admin));
        }

        $this->notificationService->notifyAdminsWithPermission(
            AdminPermission::APPROVE_CATEGORIES->value,
            new CatalogSubmissionReceivedNotification($this->displayName($category->name, 'Category'), 'Category'),
            $admins
        );
    }

    public function notifyMasterProductSubmitted(MasterProduct $product, Vendor $vendor): void
    {
        $admins = $this->adminResolver->forVendor($vendor);

        foreach ($admins as $admin) {
            event(new MasterProductSubmitted($product, $admin));
        }

        $this->notificationService->notifyAdminsWithPermission(
            AdminPermission::APPROVE_MASTER_PRODUCTS->value,
            new CatalogSubmissionReceivedNotification($this->displayName($product->name, 'Product'), 'Master Product'),
            $admins
        );
    }

    /**
     * @param  array<string, string>|string|null  $name
     */
    private function displayName(array|string|null $name, string $fallback): string
    {
        if (is_string($name)) {
            return $name;
        }

        return $name['en']
            ?? $name['ar']
            ?? current((array) $name)
            ?: $fallback;
    }
}
