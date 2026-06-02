<?php

namespace App\Services\Admin;

use App\Models\Admin;
use Illuminate\Validation\ValidationException;

class AdminZoneService
{
    /**
     * Get the currently authenticated API admin, when present.
     */
    public function currentAdmin(): ?Admin
    {
        $admin = auth('api_admin')->user();

        return $admin instanceof Admin ? $admin : null;
    }

    public function shouldRestrict(?Admin $admin = null): bool
    {
        $admin ??= $this->currentAdmin();

        return $admin instanceof Admin && ! $admin->isSuperAdmin();
    }

    /**
     * @return list<int>
     */
    public function zoneIdsFor(Admin $admin): array
    {
        $zoneIds = $admin->relationLoaded('deliveryZones')
            ? $admin->deliveryZones->modelKeys()
            : $admin->deliveryZones()->pluck('delivery_zones.id')->all();

        return array_values(array_map('intval', $zoneIds));
    }

    /**
     * @param  array<int, int|string>|null  $zoneIds
     */
    public function ensureZoneIdsAssignable(?array $zoneIds, ?Admin $admin = null): void
    {
        if (is_null($zoneIds) || ! $this->shouldRestrict($admin)) {
            return;
        }

        $admin ??= $this->currentAdmin();

        if (! $admin instanceof Admin) {
            return;
        }

        $allowedZoneIds = $this->zoneIdsFor($admin);
        $requestedZoneIds = array_values(array_map('intval', $zoneIds));
        $unauthorizedZoneIds = array_diff($requestedZoneIds, $allowedZoneIds);

        if (! empty($unauthorizedZoneIds)) {
            throw ValidationException::withMessages([
                'delivery_zones' => __('messages.unauthorized_delivery_zones'),
            ]);
        }
    }
}
