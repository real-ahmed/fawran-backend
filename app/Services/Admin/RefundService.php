<?php

namespace App\Services\Admin;

use App\DTOs\Admin\RefundRequest\RefundRequestDataDTO;
use App\DTOs\Admin\RefundRequest\RefundRequestFilterDTO;
use App\Models\Payment\RefundRequest;
use App\Traits\Paginatable;

class RefundService
{
    use Paginatable;

    public function listRefundRequests(RefundRequestFilterDTO $filters)
    {
        return RefundRequest::query()
            ->withListRelations()
            ->forAdminZones()
            ->status($filters->status)
            ->newest()
            ->cursorPaginate($this->getPerPageLimit());
    }

    public function getRefundRequest(RefundRequest $refundRequest): RefundRequest
    {
        return $refundRequest->load(['customer', 'order', 'items']);
    }

    public function resolveRefundRequest(RefundRequest $refundRequest, RefundRequestDataDTO $dto): RefundRequest
    {
        $refundRequest->update([
            'status' => $dto->status,
            'resolution' => $dto->resolution ?? $refundRequest->resolution,
        ]);

        return $refundRequest;
    }
}
