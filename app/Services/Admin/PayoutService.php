<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Models\Payment\PayoutRequest;
use App\Traits\Paginatable;
use Illuminate\Http\Request;

class PayoutService
{
    use Paginatable;

    public function listPayoutRequests(Request $request)
    {
        return PayoutRequest::query()
            ->withListRelations()
            ->forAdminZones()
            ->status($request->query('status'))
            ->newest()
            ->paginate($this->getPerPageLimit());
    }

    public function approvePayoutRequest(PayoutRequest $payoutRequest, Admin $admin): PayoutRequest
    {
        $payoutRequest->execution()->create([
            'admin_id' => $admin->id,
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
