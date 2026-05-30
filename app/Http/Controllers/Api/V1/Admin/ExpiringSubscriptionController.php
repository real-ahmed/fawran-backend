<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Admin\Vendor\ExpiringSubscriptionResource;
use App\Services\Vendor\VendorSubscriptionService;
use Illuminate\Http\Request;

class ExpiringSubscriptionController extends Controller
{
    public function __construct(
        private readonly VendorSubscriptionService $vendorSubscriptionService
    ) {}

    public function __invoke(Request $request)
    {
        $subscriptions = $this->vendorSubscriptionService->getExpiringSubscriptions(
            $request->integer('per_page', 15)
        );

        return $this->paginatedResponse(
            ExpiringSubscriptionResource::collection($subscriptions),
            __('messages.retrieved_successfully')
        );
    }
}
