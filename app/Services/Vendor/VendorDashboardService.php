<?php

namespace App\Services\Vendor;

use App\Models\Order\SubOrder;
use App\Models\Platform\OrderCommission;
use App\Models\Vendor\Vendor;
use Illuminate\Support\Carbon;

class VendorDashboardService
{
    /**
     * Get dashboard metrics for the given vendor.
     *
     * @return array<string, mixed>
     */
    public function getMetrics(int $vendorId): array
    {
        $vendor = Vendor::findOrFail($vendorId);
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        $subOrderQuery = SubOrder::where('vendor_id', $vendorId);

        return [
            'orders' => [
                'today' => (clone $subOrderQuery)->whereDate('created_at', $today)->count(),
                'this_week' => (clone $subOrderQuery)->where('created_at', '>=', $weekStart)->count(),
                'this_month' => (clone $subOrderQuery)->where('created_at', '>=', $monthStart)->count(),
                'pending' => (clone $subOrderQuery)->where('status', 'pending')->count(),
                'preparing' => (clone $subOrderQuery)->where('status', 'preparing')->count(),
            ],
            'revenue' => [
                'today' => (float) (clone $subOrderQuery)->whereDate('created_at', $today)->sum('sub_total'),
                'this_week' => (float) (clone $subOrderQuery)->where('created_at', '>=', $weekStart)->sum('sub_total'),
                'this_month' => (float) (clone $subOrderQuery)->where('created_at', '>=', $monthStart)->sum('sub_total'),
            ],
            'commissions' => [
                'this_month' => (float) OrderCommission::where('vendor_id', $vendorId)
                    ->where('created_at', '>=', $monthStart)
                    ->sum('vendorcommission_amount'),
            ],
            'rating' => [
                'average' => (float) ($vendor->average_rating ?? 0),
                'total_reviews' => (int) ($vendor->total_reviews ?? 0),
            ],
        ];
    }
}
