<?php

namespace App\Services\Admin;

use App\Events\CatalogItemSubmitted;
use App\Models\Admin;
use App\Models\Vendor\Vendor;
use Illuminate\Support\Collection;

class CatalogRealtimeNotifier
{
    public function notifySubmission(string $itemType, int $itemId, array|string $itemName, Vendor $vendor): void
    {
        foreach ($this->adminsForVendor($vendor) as $admin) {
            event(new CatalogItemSubmitted(
                $itemType,
                $itemId,
                $itemName,
                $admin
            ));
        }
    }

    /**
     * @return Collection<int, Admin>
     */
    private function adminsForVendor(Vendor $vendor): Collection
    {
        $admins = Admin::query()
            ->where('id', 1)
            ->orWhereHas('roles', fn ($query) => $query->where('name', 'Super Admin'))
            ->get();

        $zoneIds = $vendor->deliveryZones()->pluck('delivery_zone_id')->all();

        if (! empty($zoneIds)) {
            $zoneAdmins = Admin::query()
                ->whereHas('deliveryZones', fn ($query) => $query->whereIn('delivery_zones.id', $zoneIds))
                ->get();

            $admins = $admins->merge($zoneAdmins);
        }

        return $admins->unique('id')->values();
    }
}
