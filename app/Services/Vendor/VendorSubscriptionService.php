<?php

namespace App\Services\Vendor;

use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorSubscription;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class VendorSubscriptionService
{
    /**
     * Get all subscriptions for a specific vendor
     */
    public function getVendorSubscriptions(Vendor $vendor): Collection
    {
        return $vendor->subscriptions()->with('plan')->latest()->get();
    }

    /**
     * Assign a new subscription to a vendor
     */
    public function assignSubscription(Vendor $vendor, array $data): VendorSubscription
    {
        // Cancel any currently active subscription
        $vendor->subscriptions()->where('status', 'active')->update(['status' => 'expired']);

        $startsAt = isset($data['starts_at']) ? now()->parse($data['starts_at']) : now();
        $expiresAt = isset($data['expires_at']) ? now()->parse($data['expires_at']) : null;

        if (isset($data['duration_months']) && $data['duration_months']) {
            $expiresAt = $startsAt->copy()->addMonths($data['duration_months']);
        }

        $subscription = VendorSubscription::create([
            'vendor_id' => $vendor->id,
            'plan_id' => $data['plan_id'],
            'status' => 'active',
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
        ]);

        $subscription->load('plan');

        return $subscription;
    }

    /**
     * Get all expiring subscriptions across all vendors
     */
    public function getExpiringSubscriptions(int $perPage = 15): LengthAwarePaginator
    {
        // Expired or Active but expiring within 14 days
        $thresholdDate = now()->addDays(14);

        return VendorSubscription::with(['vendor.owner', 'plan'])
            ->where(function ($query) use ($thresholdDate) {
                $query->where('status', 'expired')
                      ->orWhere(function ($q) use ($thresholdDate) {
                          $q->where('status', 'active')
                            ->whereNotNull('expires_at')
                            ->where('expires_at', '<=', $thresholdDate);
                      });
            })
            ->latest('expires_at')
            ->paginate($perPage);
    }
}
