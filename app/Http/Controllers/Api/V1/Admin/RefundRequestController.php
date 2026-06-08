<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\DTOs\Admin\RefundRequest\RefundRequestDataDTO;
use App\DTOs\Admin\RefundRequest\RefundRequestFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\RefundRequest\IndexRefundRequest;
use App\Http\Requests\V1\Admin\RefundRequest\ResolveRefundRequest;
use App\Http\Resources\V1\Admin\RefundRequestResource;
use App\Models\Payment\RefundRequest;
use App\Services\Admin\RefundService;

/**
 * @group Admin - Refund Requests
 *
 * APIs for reviewing and resolving customer refund requests.
 */
class RefundRequestController extends Controller
{
    public function __construct(protected RefundService $refundService) {}

    /**
     * List Refund Requests
     *
     * Get a paginated list of all refund requests with optional status filter.
     *
     * @queryParam status string Filter by status (pending, approved, rejected, processed). Example: pending
     */
    public function index(IndexRefundRequest $request)
    {
        $dto = RefundRequestFilterDTO::fromRequest($request);
        $refundRequests = $this->refundService->listRefundRequests($dto);

        return $this->paginatedResponse($refundRequests, RefundRequestResource::collection($refundRequests->items()));
    }

    /**
     * Get Refund Request Details
     *
     * Retrieve a specific refund request with customer, order, and items.
     */
    public function show(RefundRequest $refundRequest)
    {
        return $this->successResponse(
            new RefundRequestResource($this->refundService->getRefundRequest($refundRequest))
        );
    }

    /**
     * Resolve Refund Request
     *
     * Approve or reject a refund request and set the resolution method.
     *
     * @bodyParam status string required New status (approved, rejected, processed). Example: approved
     * @bodyParam resolution string optional Resolution method (wallet_credit, gateway_refund). Example: wallet_credit
     */
    public function resolve(ResolveRefundRequest $request, RefundRequest $refundRequest)
    {
        $dto = RefundRequestDataDTO::fromRequest($request);
        $refundRequest = $this->refundService->resolveRefundRequest($refundRequest, $dto);

        return $this->successResponse(
            new RefundRequestResource($refundRequest),
            __('messages.refund_resolved_successfully')
        );
    }
}
