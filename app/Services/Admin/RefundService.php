<?php

namespace App\Services\Admin;

use App\Models\Payment\RefundRequest;
use App\Traits\Paginatable;
use Illuminate\Http\Request;

class RefundService
{
    use Paginatable;

    public function listRefundRequests(Request $request)
    {
        return RefundRequest::query()
            ->withListRelations()
            ->forAdminZones()
            ->status($request->query('status'))
            ->newest()
            ->cursorPaginate($this->getPerPageLimit());
    }

    public function getRefundRequest(RefundRequest $refundRequest): RefundRequest
    {
        return $refundRequest->load(['customer', 'order', 'items']);
    }

    public function resolveRefundRequest(RefundRequest $refundRequest, array $data): RefundRequest
    {
        $refundRequest->update([
            'status' => $data['status'],
            'resolution' => $data['resolution'] ?? $refundRequest->resolution,
        ]);

        return $refundRequest;
    }
}
