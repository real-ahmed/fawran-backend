<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\SettlementStatus;
use App\Enums\SettlementType;
use App\Enums\WalletTransactionType;
use App\Models\Payment\Settlement;
use App\Models\Platform\OrderCommission;
use App\Models\Platform\SystemSetting;
use App\Models\Vendor\Vendor;
use App\Services\Finance\WalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessVendorSettlements extends Command
{
    protected $signature = 'settlement:vendors';

    protected $description = 'Process periodic settlements for vendors — credit their wallets with earnings';

    public function __construct(private WalletService $walletService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Processing vendor settlements...');

        $cycleDays = (int) SystemSetting::cachedValue('settlement_cycle_days', '7');
        $periodEnd = now()->toDateString();
        $periodStart = now()->subDays($cycleDays)->toDateString();

        // Find vendors with delivered orders that have commission records
        // but haven't been settled yet in this period
        $vendorCommissions = OrderCommission::query()
            ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::Delivered->value))
            ->whereDoesntHave('order', function ($q) {
                // Exclude orders already linked to a settlement item
                $q->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('settlement_items')
                        ->where('reference_type', (new OrderCommission)->getMorphClass())
                        ->whereColumn('settlement_items.reference_id', 'order_commissions.id');
                });
            })
            ->select('vendor_id')
            ->selectRaw('SUM(order_commissions.vendorcommission_amount) as total_commission')
            ->selectRaw('COUNT(*) as order_count')
            ->groupBy('vendor_id')
            ->get();

        if ($vendorCommissions->isEmpty()) {
            $this->info('No unsettled vendor commissions found.');

            return self::SUCCESS;
        }

        $settlementCount = 0;

        foreach ($vendorCommissions as $vc) {
            $vendor = Vendor::with('owner')->find($vc->vendor_id);
            if (! $vendor?->owner) {
                continue;
            }

            DB::transaction(function () use ($vendor, $periodStart, $periodEnd, &$settlementCount) {
                // Get the actual unsettled commission records
                $commissions = OrderCommission::query()
                    ->where('vendor_id', $vendor->id)
                    ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::Delivered->value))
                    ->whereDoesntHave('order', function ($q) {
                        $q->whereExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('settlement_items')
                                ->where('reference_type', (new OrderCommission)->getMorphClass())
                                ->whereColumn('settlement_items.reference_id', 'order_commissions.id');
                        });
                    })
                    ->get();

                if ($commissions->isEmpty()) {
                    return;
                }

                // Calculate vendor earnings: sub_total - commission per sub_order
                $totalVendorEarnings = 0;
                $totalCommissions = 0;

                foreach ($commissions as $commission) {
                    $order = $commission->order;
                    $subOrder = $order->subOrders()->where('vendor_id', $vendor->id)->first();

                    if ($subOrder) {
                        $vendorNet = (float) $subOrder->sub_total - (float) $commission->vendorcommission_amount;
                        $totalVendorEarnings += max($vendorNet, 0);
                    }

                    $totalCommissions += (float) $commission->vendorcommission_amount;
                }

                // Create settlement
                $settlement = Settlement::create([
                    'settlement_type' => SettlementType::Vendor,
                    'target_id' => $vendor->id,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'total_gross' => $totalVendorEarnings + $totalCommissions,
                    'total_deductions' => $totalCommissions,
                    'total_net_exchange' => $totalVendorEarnings,
                    'status' => SettlementStatus::Completed,
                ]);

                // Create settlement items
                foreach ($commissions as $commission) {
                    $settlement->items()->create([
                        'reference_type' => $commission->getMorphClass(),
                        'reference_id' => $commission->getKey(),
                        'amount' => $commission->vendorcommission_amount,
                    ]);
                }

                // Credit vendor wallet
                if ($totalVendorEarnings > 0) {
                    $this->walletService->deposit(
                        $vendor->owner,
                        $totalVendorEarnings,
                        WalletTransactionType::CommissionEarning,
                        $settlement,
                    );
                }

                $settlementCount++;
            });
        }

        $this->info("Processed {$settlementCount} vendor settlements.");

        return self::SUCCESS;
    }
}
