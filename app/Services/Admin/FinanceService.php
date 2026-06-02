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
        $admin = auth('api_admin')->user();
        $isSuperAdmin = $admin && $admin->isSuperAdmin();

        if ($isSuperAdmin) {
            $platformBalance = $this->platformWalletService->getBalance();
        } else {
            $totalRevenue = OrderCommission::forAdminZones()->sum('net_platform_profit');
            $transferredPayouts = PayoutRequest::forAdminZones()->where('status', PayoutRequestStatus::Transferred)->sum('amount');
            $platformBalance = [
                'total_revenue' => $totalRevenue,
                'current_balance' => $totalRevenue - $transferredPayouts,
            ];
        }

        return [
            'platform' => $platformBalance,
            'commissions' => [
                'total_count' => OrderCommission::forAdminZones()->count(),
                'total_platform_profit' => OrderCommission::forAdminZones()->sum('net_platform_profit'),
                'total_vendorcommission' => OrderCommission::forAdminZones()->sum('vendorcommission_amount'),
                'total_delivery_share' => OrderCommission::forAdminZones()->sum('app_delivery_share'),
            ],
            'cash' => [
                'unsettled_total' => CourierCashCollection::forAdminZones()
                    ->where('is_settled', false)
                    ->sum('amount_owed_to_platform'),
                'unsettled_count' => CourierCashCollection::forAdminZones()
                    ->where('is_settled', false)
                    ->count(),
                'total_collected' => CourierCashCollection::forAdminZones()->sum('amount_collected'),
            ],
            'payouts' => [
                'pending_count' => PayoutRequest::forAdminZones()->where('status', PayoutRequestStatus::Pending)->count(),
                'pending_total' => PayoutRequest::forAdminZones()->where('status', PayoutRequestStatus::Pending)->sum('amount'),
                'transferred_total' => PayoutRequest::forAdminZones()->where('status', PayoutRequestStatus::Transferred)->sum('amount'),
            ],
            'settlements' => [
                'pending_count' => Settlement::forAdminZones()->where('status', SettlementStatus::Pending)->count(),
                'pending_total' => Settlement::forAdminZones()->where('status', SettlementStatus::Pending)->sum('total_net_exchange'),
                'completed_count' => Settlement::forAdminZones()->where('status', SettlementStatus::Completed)->count(),
            ],
        ];
    }
}
