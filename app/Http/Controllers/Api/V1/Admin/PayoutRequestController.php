<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PayoutRequestResource;
use App\Models\Payment\PayoutRequest;
use App\Services\Admin\PayoutService;
use Illuminate\Http\Request;

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
    public function index(Request $request)
    {
        return PayoutRequestResource::collection($this->payoutService->listPayoutRequests($request));
    }

    /**
     * Approve Payout Request
     *
     * Approve a payout request and record the execution.
     */
    public function approve(PayoutRequest $payoutRequest)
    {
        $admin = auth('api_admin')->user();
        $payoutRequest = $this->payoutService->approvePayoutRequest($payoutRequest, $admin);

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
