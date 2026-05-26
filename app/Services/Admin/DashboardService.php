<?php

namespace App\Services\Admin;

use App\Models\Catalog\VendorBrandSubmission;
use App\Models\Catalog\VendorCategorySubmission;
use App\Models\Courier\Courier;
use App\Models\Order\Order;
use App\Models\Platform\PlatformWallet;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Collection;

class DashboardService
{
    /**
     * Get key platform metrics for the admin dashboard.
     *
     * @return array{orders: array, vendors: array, couriers: array, revenue: array}
     */
    public function getMetrics(): array
    {
        $platformWallet = PlatformWallet::first();

        return [
            'orders' => [
                'total' => Order::forAdminZones()->count(),
                'pending' => Order::forAdminZones()->where('status', 'pending')->count(),
                'processing' => Order::forAdminZones()->where('status', 'processing')->count(),
                'delivered' => Order::forAdminZones()->where('status', 'delivered')->count(),
                'cancelled' => Order::forAdminZones()->where('status', 'cancelled')->count(),
            ],
            'vendors' => [
                'total' => Vendor::forAdminZones()->count(),
                'active' => Vendor::forAdminZones()->where('is_active', true)->count(),
            ],
            'couriers' => [
                'total' => Courier::forAdminZones()->count(),
                'online' => Courier::forAdminZones()->where('is_online', true)->count(),
                'pending_approval' => Courier::forAdminZones()->whereDoesntHave('approval')->count(),
            ],
            'revenue' => [
                'total_revenue' => $platformWallet?->total_revenue ?? '0.00',
                'current_balance' => $platformWallet?->current_balance ?? '0.00',
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
            'brands' => VendorBrandSubmission::with(['brand', 'vendor'])
                ->forAdminZones()
                ->where('status', 'pending')
                ->latest()
                ->get(),
            'categories' => VendorCategorySubmission::with(['category', 'vendor'])
                ->forAdminZones()
                ->where('status', 'pending')
                ->latest()
                ->get(),
            'couriers' => Courier::with(['user', 'deliveryZone', 'document'])
                ->forAdminZones()
                ->whereDoesntHave('approval')
                ->latest('created_at')
                ->get(),
        ];
    }
}
