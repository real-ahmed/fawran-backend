<?php

namespace App\Services\Admin;

use App\Models\Platform\OrderCommission;
use App\Models\Platform\PlatformWallet;

class FinanceService
{
    /**
     * Get platform financial overview.
     *
     * @return array{platform: array, commissions: array}
     */
    public function getOverview(): array
    {
        $platformWallet = PlatformWallet::first();

        return [
            'platform' => [
                'total_revenue' => $platformWallet?->total_revenue ?? '0.00',
                'current_balance' => $platformWallet?->current_balance ?? '0.00',
            ],
            'commissions' => [
                'total_count' => OrderCommission::count(),
                'total_platform_profit' => OrderCommission::sum('net_platform_profit'),
                'total_vendorcommission' => OrderCommission::sum('vendorcommission_amount'),
                'total_delivery_share' => OrderCommission::sum('app_delivery_share'),
            ],
        ];
    }
}
