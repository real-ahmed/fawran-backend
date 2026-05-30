<?php

namespace App\Services\Finance;

use App\Models\Platform\SubscriptionPlan;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionPlanService
{
    /**
     * Get all subscription plans
     */
    public function getAllPlans(): Collection
    {
        return SubscriptionPlan::latest()->get();
    }

    /**
     * Create a new subscription plan
     */
    public function createPlan(array $data): SubscriptionPlan
    {
        return SubscriptionPlan::create($data);
    }

    /**
     * Update an existing subscription plan
     */
    public function updatePlan(SubscriptionPlan $plan, array $data): SubscriptionPlan
    {
        $plan->update($data);
        return $plan;
    }

    /**
     * Delete a subscription plan
     */
    public function deletePlan(SubscriptionPlan $plan): void
    {
        $plan->delete();
    }
}
