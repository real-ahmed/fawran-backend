<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Finance\StoreSubscriptionPlanRequest;
use App\Http\Requests\V1\Admin\Finance\UpdateSubscriptionPlanRequest;
use App\Http\Resources\V1\Admin\Finance\SubscriptionPlanResource;
use App\Models\Platform\SubscriptionPlan;
use App\Services\Finance\SubscriptionPlanService;

class SubscriptionPlanController extends Controller
{
    public function __construct(
        private readonly SubscriptionPlanService $subscriptionPlanService
    ) {}

    public function index()
    {
        return $this->successResponse(
            SubscriptionPlanResource::collection($this->subscriptionPlanService->getAllPlans()),
            __('messages.retrieved_successfully')
        );
    }

    public function store(StoreSubscriptionPlanRequest $request)
    {
        $plan = $this->subscriptionPlanService->createPlan($request->validated());

        return $this->successResponse(
            new SubscriptionPlanResource($plan),
            __('messages.created_successfully'),
            201
        );
    }

    public function show(SubscriptionPlan $subscriptionPlan)
    {
        return $this->successResponse(
            new SubscriptionPlanResource($subscriptionPlan),
            __('messages.retrieved_successfully')
        );
    }

    public function update(UpdateSubscriptionPlanRequest $request, SubscriptionPlan $subscriptionPlan)
    {
        $plan = $this->subscriptionPlanService->updatePlan($subscriptionPlan, $request->validated());

        return $this->successResponse(
            new SubscriptionPlanResource($plan),
            __('messages.updated_successfully')
        );
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        $this->subscriptionPlanService->deletePlan($subscriptionPlan);

        return $this->successResponse(null, __('messages.deleted_successfully'));
    }
}
