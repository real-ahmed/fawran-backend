<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Api\V1\Vendor\Concerns\ResolvesVendorContext;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\SubscriptionPlanResource;
use App\Http\Resources\V1\VendorSubscriptionResource;
use App\Services\Vendor\VendorSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorSubscriptionController extends Controller
{
    use ResolvesVendorContext;

    public function __construct(
        private readonly VendorSubscriptionService $service
    ) {}

    public function current(Request $request): JsonResponse
    {
        $subscription = $this->service->getActiveSubscription($this->vendor($request));

        return $this->successResponse($subscription ? new VendorSubscriptionResource($subscription) : null);
    }

    public function plans(): JsonResponse
    {
        $plans = $this->service->getAvailablePlans();

        return $this->successResponse(SubscriptionPlanResource::collection($plans));
    }
}
