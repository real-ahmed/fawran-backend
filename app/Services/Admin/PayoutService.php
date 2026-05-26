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
        $query = PayoutRequest::with(['user', 'execution'])->forAdminZones();

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        return $query->latest('created_at')->paginate($this->getPerPageLimit());
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
