<?php

namespace App\Console\Commands;

use App\Enums\SettlementStatus;
use App\Enums\SettlementType;
use App\Enums\WalletTransactionType;
use App\Models\Courier\Courier;
use App\Models\Payment\CourierCashCollection;
use App\Models\Payment\Settlement;
use App\Models\Platform\SystemSetting;
use App\Services\Finance\CashCollectionService;
use App\Services\Finance\WalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessCourierSettlements extends Command
{
    protected $signature = 'settlement:couriers';

    protected $description = 'Process periodic settlements for couriers with unsettled cash collections';

    public function __construct(
        private WalletService $walletService,
        private CashCollectionService $cashService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Processing courier settlements...');

        $autoDeduct = SystemSetting::cachedValue('courier_cod_wallet_deduction_enabled', 'true') === 'true';

        // Find couriers with unsettled cash
        $courierIds = CourierCashCollection::query()
            ->where('is_settled', false)
            ->distinct()
            ->pluck('courier_id');

        if ($courierIds->isEmpty()) {
            $this->info('No unsettled courier cash collections found.');

            return self::SUCCESS;
        }

        $settlementCount = 0;

        foreach ($courierIds as $courierId) {
            $courier = Courier::with('user')->find($courierId);
            if (! $courier) {
                continue;
            }

            DB::transaction(function () use ($courier, $autoDeduct, &$settlementCount) {
                $unsettledTotal = $this->cashService->getUnsettledTotal($courier);

                if ($unsettledTotal <= 0) {
                    return;
                }

                $periodEnd = now()->toDateString();
                $cycleDays = (int) SystemSetting::cachedValue('settlement_cycle_days', '7');
                $periodStart = now()->subDays($cycleDays)->toDateString();

                // Create settlement record
                $settlement = Settlement::create([
                    'settlement_type' => SettlementType::Courier,
                    'target_id' => $courier->id,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'total_gross' => $unsettledTotal,
                    'total_deductions' => 0,
                    'total_net_exchange' => $unsettledTotal,
                    'status' => SettlementStatus::Pending,
                ]);

                // Link unsettled collections as settlement items
                $collections = CourierCashCollection::query()
                    ->where('courier_id', $courier->id)
                    ->where('is_settled', false)
                    ->get();

                foreach ($collections as $collection) {
                    $settlement->items()->create([
                        'reference_type' => $collection->getMorphClass(),
                        'reference_id' => $collection->getKey(),
                        'amount' => $collection->amount_owed_to_platform,
                    ]);
                }

                // Auto-deduct from wallet if enabled
                if ($autoDeduct && $courier->user) {
                    $deducted = $this->walletService->withdrawAvailable(
                        $courier->user,
                        $unsettledTotal,
                        WalletTransactionType::CodDeduction,
                        $settlement,
                    );

                    $settlement->update([
                        'total_deductions' => $deducted,
                        'total_net_exchange' => $unsettledTotal - $deducted,
                    ]);

                    // If fully deducted, auto-complete
                    if ($deducted >= $unsettledTotal) {
                        $settlement->update(['status' => SettlementStatus::Completed]);
                        $this->cashService->settleCollections($courier);

                        // Unblock COD if courier was blocked
                        if ($courier->cod_blocked) {
                            $courier->update(['cod_blocked' => false]);
                        }
                    }
                }

                $settlementCount++;
            });
        }

        $this->info("Processed {$settlementCount} courier settlements.");

        return self::SUCCESS;
    }
}
