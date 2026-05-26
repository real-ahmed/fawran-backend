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
                'total' => Order::count(),
                'pending' => Order::where('status', 'pending')->count(),
                'processing' => Order::where('status', 'processing')->count(),
                'delivered' => Order::where('status', 'delivered')->count(),
                'cancelled' => Order::where('status', 'cancelled')->count(),
            ],
            'vendors' => [
                'total' => Vendor::count(),
                'active' => Vendor::where('is_active', true)->count(),
            ],
            'couriers' => [
                'total' => Courier::count(),
                'online' => Courier::where('is_online', true)->count(),
                'pending_approval' => Courier::whereDoesntHave('approval')->count(),
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
                ->where('status', 'pending')
                ->latest()
                ->get(),
            'categories' => VendorCategorySubmission::with(['category', 'vendor'])
                ->where('status', 'pending')
                ->latest()
                ->get(),
            'couriers' => Courier::with(['user', 'deliveryZone', 'document'])
                ->whereDoesntHave('approval')
                ->latest('created_at')
                ->get(),
        ];
    }
}
