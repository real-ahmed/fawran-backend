<?php

namespace App\Services\Admin;

use App\Models\Catalog\VendorBrandSubmission;
use App\Models\Catalog\VendorCategorySubmission;
use App\Models\Courier\Courier;
use App\Models\Order\Order;
use App\Models\Platform\PlatformWallet;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    /**
     * Get key platform metrics for the admin dashboard.
     *
     * @return array{orders: array, vendors: array, couriers: array, revenue: array}
     */
    public function getMetrics(): array
    {
        return Cache::flexible(
            $this->metricsCacheKey(),
            [30, 120],
            fn (): array => $this->buildMetrics()
        );
    }

    /**
     * Build key platform metrics for the admin dashboard.
     *
     * @return array{orders: array, vendors: array, couriers: array, revenue: array}
     */
    private function buildMetrics(): array
    {
        $platformWallet = PlatformWallet::first();

        return [
            'orders' => [
                'total' => Order::forAdminZones()->count(),
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
            'revenue' => [
                'total_revenue' => $platformWallet?->total_revenue ?? '0.00',
                'current_balance' => $platformWallet?->current_balance ?? '0.00',
            ],
        ];
    }

    private function metricsCacheKey(): string
    {
        $admin = auth('api_admin')->user();

        if (! $admin) {
            return 'admin.dashboard.metrics.guest';
        }

        if ($admin->hasRole('Super Admin')) {
            return 'admin.dashboard.metrics.super_admin';
        }

        $zoneIds = $admin->deliveryZones()
            ->pluck('delivery_zones.id')
            ->sort()
            ->values()
            ->implode('.');

        return sprintf('admin.dashboard.metrics.admin.%s.zones.%s', $admin->id, $zoneIds ?: 'none');
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
            'couriers' => Courier::with(['user', 'document'])
                ->forAdminZones()
                ->whereDoesntHave('approval')
                ->latest('created_at')
                ->get(),
        ];
    }
}
