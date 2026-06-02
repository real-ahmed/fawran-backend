<?php

namespace App\Services\Admin;

use App\DTOs\Admin\Settlement\SettlementDataDTO;
use App\DTOs\Admin\Settlement\SettlementFilterDTO;
use App\Enums\ExecutionMethod;
use App\Enums\SettlementStatus;
use App\Enums\SettlementType;
use App\Enums\WalletTransactionType;
use App\Models\Admin;
use App\Models\Courier\Courier;
use App\Models\Payment\Settlement;
use App\Services\Finance\CashCollectionService;
use App\Services\Finance\PlatformWalletService;
use App\Services\Finance\WalletService;
use App\Traits\Paginatable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SettlementService
{
    use Paginatable;

    public function __construct(
        private WalletService $walletService,
        private CashCollectionService $cashService,
        private PlatformWalletService $platformWalletService,
    ) {}

    public function listSettlements(SettlementFilterDTO $filters)
    {
        return Settlement::query()
            ->withListRelations()
            ->forAdminZones()
            ->type($filters->settlement_type)
            ->status($filters->status)
            ->newest()
            ->cursorPaginate($this->getPerPageLimit());
    }

    public function getSettlement(Settlement $settlement): Settlement
    {
        $settlement->ensureVisibleToAdminZones();

        return $settlement->load(['items', 'execution', 'note']);
    }

    public function executeSettlement(Settlement $settlement, SettlementDataDTO $dto, ?Admin $admin = null): Settlement
    {
        $settlement->ensureVisibleToAdminZones();

        if ($settlement->status === SettlementStatus::Completed) {
            throw new RuntimeException('Settlement is already completed.');
        }

        $admin ??= auth('api_admin')->user();

        return DB::transaction(function () use ($settlement, $admin, $dto) {
            $settlement->execution()->create([
                'admin_id' => $admin?->id,
                'execution_method' => $dto->execution_method,
                'executed_at' => now(),
            ]);

            if (! empty($dto->notes)) {
                $settlement->note()->updateOrCreate([], ['notes' => $dto->notes]);
            }

            // Handle wallet-based execution for courier settlements
            if (
                $dto->execution_method === ExecutionMethod::Wallet->value
                && $settlement->settlement_type === SettlementType::Courier
            ) {
                $courier = Courier::with('user')->find($settlement->target_id);
                if ($courier?->user) {
                    $this->walletService->withdraw(
                        $courier->user,
                        (float) $settlement->total_net_exchange,
                        WalletTransactionType::CodDeduction,
                        $settlement,
                    );
                }
            }

            // Settle related courier cash collections
            if ($settlement->settlement_type === SettlementType::Courier) {
                $courier = Courier::find($settlement->target_id);
                if ($courier) {
                    $this->cashService->settleCollections($courier);

                    // Unblock COD if was blocked
                    if ($courier->cod_blocked) {
                        $courier->update(['cod_blocked' => false]);
                    }
                }
            }

            $settlement->update(['status' => SettlementStatus::Completed]);

            return $settlement->load(['execution', 'note']);
        });
    }
}
