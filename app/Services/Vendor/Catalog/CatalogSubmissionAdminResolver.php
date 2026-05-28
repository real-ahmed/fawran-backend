<?php

namespace App\Services\Vendor\Catalog;

use App\Models\Admin;
use App\Models\Vendor\Vendor;
use Illuminate\Support\Collection;

class CatalogSubmissionAdminResolver
{
    /**
     * @return Collection<int, Admin>
     */
    public function forVendor(Vendor $vendor): Collection
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
