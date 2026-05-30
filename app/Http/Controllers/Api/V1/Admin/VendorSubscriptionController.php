<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Vendor\AssignVendorSubscriptionRequest;
use App\Http\Resources\V1\Admin\Vendor\VendorSubscriptionResource;
use App\Models\Vendor\Vendor;
use App\Services\Vendor\VendorSubscriptionService;

class VendorSubscriptionController extends Controller
{
    public function __construct(
        private readonly VendorSubscriptionService $vendorSubscriptionService
    ) {}

    public function index(Vendor $vendor)
    {
        return $this->successResponse(
            VendorSubscriptionResource::collection(
                $this->vendorSubscriptionService->getVendorSubscriptions($vendor)
            ),
            __('messages.retrieved_successfully')
        );
    }

    public function store(AssignVendorSubscriptionRequest $request, Vendor $vendor)
    {
        $subscription = $this->vendorSubscriptionService->assignSubscription($vendor, $request->validated());

        return $this->successResponse(
            new VendorSubscriptionResource($subscription),
            __('messages.assigned_successfully'),
            201
        );
    }
}
