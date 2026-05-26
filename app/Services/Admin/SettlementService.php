<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Models\Payment\Settlement;
use App\Traits\Paginatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettlementService
{
    use Paginatable;

    public function listSettlements(Request $request)
    {
        $query = Settlement::with(['execution', 'note'])->forAdminZones();

        if ($request->filled('settlement_type')) {
            $query->where('settlement_type', $request->query('settlement_type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        return $query->latest('created_at')->paginate($this->getPerPageLimit());
    }

    public function getSettlement(Settlement $settlement): Settlement
    {
        return $settlement->load(['items', 'execution', 'note']);
    }

    public function executeSettlement(Settlement $settlement, Admin $admin, array $data): Settlement
    {
        return DB::transaction(function () use ($settlement, $admin, $data) {
            $settlement->execution()->create([
                'admin_id' => $admin->id,
                'execution_method' => $data['execution_method'],
                'executed_at' => now(),
            ]);

            if (! empty($data['notes'])) {
                $settlement->note()->updateOrCreate([], ['notes' => $data['notes']]);
            }

            $settlement->update(['status' => 'completed']);

            return $settlement->load(['execution', 'note']);
        });
    }
}
