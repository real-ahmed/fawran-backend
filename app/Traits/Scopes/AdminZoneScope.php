<?php

namespace App\Traits\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait AdminZoneScope
{
    /**
     * Scope a query to only include records relevant to the authenticated admin's zones.
     * Super Admins bypass this scope.
     */
    public function scopeForAdminZones(Builder $query): Builder
    {
        if (auth('api_admin')->check()) {
            $admin = auth('api_admin')->user();
            
            if ($admin && !$admin->hasRole('Super Admin')) {
                $zoneIds = $admin->deliveryZones()->pluck('delivery_zones.id')->toArray();
                
                if (empty($zoneIds)) {
                    // If the admin has no zones assigned, they should see no data.
                    $query->whereRaw('1 = 0');
                } else {
                    $this->applyZoneFilter($query, $zoneIds);
                }
            }
        }

        return $query;
    }

    /**
     * Apply the specific zone filter logic for the model.
     * 
     * @param Builder $query
     * @param array $zoneIds
     */
    abstract protected function applyZoneFilter(Builder $query, array $zoneIds): void;
}
