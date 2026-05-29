<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\DTOs\Admin\PayoutRequest\PayoutRequestFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\PayoutRequest\IndexPayoutRequest;
use App\Http\Resources\V1\Admin\PayoutRequestResource;
use App\Models\Payment\PayoutRequest;
use App\Services\Admin\PayoutService;

/**
 * @group Admin - Payout Requests
 *
 * APIs for managing vendor/courier withdrawal requests.
 */
class PayoutRequestController extends Controller
{
    public function __construct(protected PayoutService $payoutService) {}

    /**
     * List Payout Requests
     *
     * Get a paginated list of all payout requests with optional status filter.
     *
     * @queryParam status string Filter by status (pending, transferred, rejected). Example: pending
     */
    public function index(IndexPayoutRequest $request)
    {
        $dto = PayoutRequestFilterDTO::fromRequest($request);

        return PayoutRequestResource::collection($this->payoutService->listPayoutRequests($dto));
    }

    /**
     * Approve Payout Request
     *
     * Approve a payout request and record the execution.
     */
    public function approve(PayoutRequest $payoutRequest)
    {
        $payoutRequest = $this->payoutService->approvePayoutRequest($payoutRequest);

        return $this->successResponse(
            new PayoutRequestResource($payoutRequest),
            __('messages.payout_approved_successfully')
        );
    }

    /**
     * Reject Payout Request
     *
     * Reject a payout request.
     */
    public function reject(PayoutRequest $payoutRequest)
    {
        $payoutRequest = $this->payoutService->rejectPayoutRequest($payoutRequest);

        return $this->successResponse(
            new PayoutRequestResource($payoutRequest),
            __('messages.payout_rejected_successfully')
        );
    }
}
