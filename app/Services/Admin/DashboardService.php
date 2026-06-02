<?php

namespace App\Services\Admin;

use App\Models\Catalog\VendorBrandSubmission;
use App\Models\Catalog\VendorCategorySubmission;
use App\Models\Courier\Courier;
use App\Models\Order\Order;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Collection;

class DashboardService
{
    /**
     * Get key platform metrics for the admin dashboard.
     *
     * @return array{orders: array, vendors: array, couriers: array}
     */
    public function getMetrics(): array
    {
        return $this->buildMetrics();
    }

    /**
     * Build key platform metrics for the admin dashboard.
     *
     * @return array{orders: array, vendors: array, couriers: array}
     */
    private function buildMetrics(): array
    {
        return [
            'orders' => [
                'total' => Order::forAdminZones()->count(),
                'total_products' => Order::forAdminZones()->sum('total_products'),
                'pending' => Order::forAdminZones()->status('pending')->count(),
                'processing' => Order::forAdminZones()->status('processing')->count(),
                'delivered' => Order::forAdminZones()->status('delivered')->count(),
                'cancelled' => Order::forAdminZones()->status('cancelled')->count(),
            ],
            'vendors' => [
                'total' => Vendor::forAdminZones()->count(),
                'active' => Vendor::forAdminZones()->active(true)->count(),
            ],
            'couriers' => [
                'total' => Courier::forAdminZones()->count(),
                'online' => Courier::forAdminZones()->online(true)->count(),
                'pending_approval' => Courier::forAdminZones()->approvalStatus('pending')->count(),
            ],
        ];
    }

    /**
     * Get all items pending admin approval.
     *
     * @return array{brands: Collection, categories: Collection, couriers: Collection}
     */
    public function getPendingApprovals(): array
    {
        return [
            'brands' => VendorBrandSubmission::query()
                ->withApprovalRelations()
                ->forAdminZones()
                ->pending()
                ->newest()
                ->get(),
            'categories' => VendorCategorySubmission::query()
                ->withApprovalRelations()
                ->forAdminZones()
                ->pending()
                ->newest()
                ->get(),
            'couriers' => Courier::query()
                ->with(['user', 'document'])
                ->forAdminZones()
                ->approvalStatus('pending')
                ->newest()
                ->get(),
        ];
    }
}
