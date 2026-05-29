<?php

namespace App\Services\Admin;

use App\DTOs\Admin\Settlement\SettlementDataDTO;
use App\DTOs\Admin\Settlement\SettlementFilterDTO;
use App\Models\Admin;
use App\Models\Payment\Settlement;
use App\Traits\Paginatable;
use Illuminate\Support\Facades\DB;

class SettlementService
{
    use Paginatable;

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
        return $settlement->load(['items', 'execution', 'note']);
    }

    public function executeSettlement(Settlement $settlement, SettlementDataDTO $dto, ?Admin $admin = null): Settlement
    {
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

            $settlement->update(['status' => 'completed']);

            return $settlement->load(['execution', 'note']);
        });
    }
}
