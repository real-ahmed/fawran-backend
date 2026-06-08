<?php

namespace App\Services\Vendor;

use App\Models\Platform\SubscriptionPlan;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorSubscription;
use Illuminate\Database\Eloquent\Collection;

class VendorSubscriptionService
{
    public function getActiveSubscription(Vendor $vendor): ?VendorSubscription
    {
        return $vendor->activeSubscription()->with('plan')->first();
    }

    public function getAvailablePlans(): Collection
    {
        return SubscriptionPlan::where('is_active', true)
            ->orderBy('monthly_price')
            ->get();
    }
}
