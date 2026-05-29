<?php

namespace App\Services\Admin;

use App\DTOs\Admin\PayoutRequest\PayoutRequestFilterDTO;
use App\Models\Admin;
use App\Models\Payment\PayoutRequest;
use App\Traits\Paginatable;

class PayoutService
{
    use Paginatable;

    public function listPayoutRequests(PayoutRequestFilterDTO $filters)
    {
        return PayoutRequest::query()
            ->withListRelations()
            ->forAdminZones()
            ->status($filters->status)
            ->newest()
            ->cursorPaginate($this->getPerPageLimit());
    }

    public function approvePayoutRequest(PayoutRequest $payoutRequest, ?Admin $admin = null): PayoutRequest
    {
        $admin ??= auth('api_admin')->user();

        $payoutRequest->execution()->create([
            'admin_id' => $admin?->id,
            'executed_at' => now(),
        ]);

        $payoutRequest->update(['status' => 'transferred']);

        return $payoutRequest->load('execution');
    }

    public function rejectPayoutRequest(PayoutRequest $payoutRequest): PayoutRequest
    {
        $payoutRequest->update(['status' => 'rejected']);

        return $payoutRequest;
    }
}
