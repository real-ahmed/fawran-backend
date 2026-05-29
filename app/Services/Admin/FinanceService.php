<?php

namespace App\Services\Admin;

use App\Enums\PayoutRequestStatus;
use App\Enums\SettlementStatus;
use App\Models\Payment\CourierCashCollection;
use App\Models\Payment\PayoutRequest;
use App\Models\Payment\Settlement;
use App\Models\Platform\OrderCommission;
use App\Services\Finance\PlatformWalletService;

class FinanceService
{
    public function __construct(private PlatformWalletService $platformWalletService) {}

    /**
     * Get comprehensive platform financial overview.
     *
     * @return array{platform: array, commissions: array, cash: array, payouts: array, settlements: array}
     */
    public function getOverview(): array
    {
        return [
            'platform' => $this->platformWalletService->getBalance(),
            'commissions' => [
                'total_count' => OrderCommission::count(),
                'total_platform_profit' => OrderCommission::sum('net_platform_profit'),
                'total_vendorcommission' => OrderCommission::sum('vendorcommission_amount'),
                'total_delivery_share' => OrderCommission::sum('app_delivery_share'),
            ],
            'cash' => [
                'unsettled_total' => CourierCashCollection::query()
                    ->where('is_settled', false)
                    ->sum('amount_owed_to_platform'),
                'unsettled_count' => CourierCashCollection::query()
                    ->where('is_settled', false)
                    ->count(),
                'total_collected' => CourierCashCollection::sum('amount_collected'),
            ],
            'payouts' => [
                'pending_count' => PayoutRequest::where('status', PayoutRequestStatus::Pending)->count(),
                'pending_total' => PayoutRequest::where('status', PayoutRequestStatus::Pending)->sum('amount'),
                'transferred_total' => PayoutRequest::where('status', PayoutRequestStatus::Transferred)->sum('amount'),
            ],
            'settlements' => [
                'pending_count' => Settlement::where('status', SettlementStatus::Pending)->count(),
                'pending_total' => Settlement::where('status', SettlementStatus::Pending)->sum('total_net_exchange'),
                'completed_count' => Settlement::where('status', SettlementStatus::Completed)->count(),
            ],
        ];
    }
}
