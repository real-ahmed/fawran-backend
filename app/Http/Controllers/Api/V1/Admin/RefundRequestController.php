<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RefundRequest\IndexRefundRequest;
use App\Http\Requests\Admin\RefundRequest\ResolveRefundRequest;
use App\Http\Resources\Admin\RefundRequestResource;
use App\Models\Payment\RefundRequest;
use App\Services\Admin\RefundService;
use Illuminate\Http\Request;

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
        return RefundRequestResource::collection($this->refundService->listRefundRequests($request));
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
        $refundRequest = $this->refundService->resolveRefundRequest($refundRequest, $request->validated());

        return $this->successResponse(
            new RefundRequestResource($refundRequest),
            __('messages.refund_resolved_successfully')
        );
    }
}
