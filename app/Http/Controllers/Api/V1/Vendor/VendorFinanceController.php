<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Api\V1\Vendor\Concerns\ResolvesVendorContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\Finance\StorePayoutRequest;
use App\Http\Resources\V1\PayoutRequestResource;
use App\Http\Resources\V1\WalletResource;
use App\Services\Vendor\VendorFinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorFinanceController extends Controller
{
    use ResolvesVendorContext;

    public function __construct(
        private readonly VendorFinanceService $service
    ) {}

    public function wallet(Request $request): JsonResponse
    {
        $wallet = $this->service->getWallet($this->vendor($request));

        return $this->successResponse(new WalletResource($wallet));
    }

    public function payoutRequests(Request $request): JsonResponse
    {
        $requests = $this->service->listPayoutRequests($this->vendor($request));

        return $this->paginatedResponse($requests, PayoutRequestResource::collection($requests->items()));
    }

    public function storePayoutRequest(StorePayoutRequest $request): JsonResponse
    {
        $payoutRequest = $this->service->createPayoutRequest($this->vendor($request), $request->validated());

        return $this->successResponse(new PayoutRequestResource($payoutRequest), __('messages.payout_request_created'), 201);
    }
}
