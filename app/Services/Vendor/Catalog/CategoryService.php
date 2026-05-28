<?php

namespace App\Services\Vendor\Catalog;

use App\Enums\AdminPermission;
use App\Events\Catalog\CategorySubmitted;
use App\Models\Catalog\Category;
use App\Models\Vendor\Vendor;
use App\Notifications\Admin\CatalogSubmissionReceivedNotification;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function __construct(
        private readonly CatalogSubmissionAdminResolver $adminResolver,
        private readonly AdminNotificationService $notificationService
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function submit(array $data, Vendor $vendor): Category
    {
        return DB::transaction(function () use ($data, $vendor): Category {
            $category = Category::create([
                'name' => $data['name'],
                'is_active' => false,
            ]);

            if (isset($data['parent_category_id'])) {
                $category->hierarchy()->create([
                    'parent_category_id' => $data['parent_category_id'],
                ]);
            }

            if (! empty($data['icon_class'])) {
                $category->icon()->create([
                    'icon_class' => $data['icon_class'],
                ]);
            }

            $category->vendorSubmission()->create([
                'vendor_id' => $vendor->id,
                'status' => 'pending',
            ]);

            $this->broadcastSubmission($category, $vendor);

            return $category->load(['hierarchy', 'icon']);
        });
    }

    private function broadcastSubmission(Category $category, Vendor $vendor): void
    {
        $admins = $this->adminResolver->forVendor($vendor);

        foreach ($admins as $admin) {
            event(new CategorySubmitted($category, $admin));
        }

        $this->notificationService->notifyAdminsWithPermission(
            AdminPermission::APPROVE_CATEGORIES->value,
            new CatalogSubmissionReceivedNotification($category->name['en'] ?? 'Category', 'Category'),
            $admins
        );
    }
}
