<?php

namespace App\Observers\Courier;

use App\Models\Courier\Courier;

class CourierObserver
{
    /**
     * Handle the Courier "created" event.
     */
    public function created(Courier $courier): void
    {
        $adminsToNotify = collect();

        $superAdmins = \App\Models\Admin::where('id', 1)
            ->orWhereHas('roles', fn($q) => $q->where('name', 'Super Admin'))
            ->get();
            
        $adminsToNotify = $adminsToNotify->merge($superAdmins);

        if ($courier->location) {
            $lon = $courier->location->longitude;
            $lat = $courier->location->latitude;

            $zoneAdmins = \App\Models\Admin::whereHas('deliveryZones', function ($q) use ($lon, $lat) {
                $q->whereRaw("ST_Contains(delivery_zones.polygon, ST_GeomFromText(CONCAT('POINT(', ?, ' ', ?, ')')))", [$lon, $lat]);
            })->get();
            
            $adminsToNotify = $adminsToNotify->merge($zoneAdmins);
        }

        foreach ($adminsToNotify->unique('id') as $admin) {
            event(new \App\Events\CourierApplicationSubmitted($courier, $admin));
        }
    }

    /**
     * Handle the Courier "updated" event.
     */
    public function updated(Courier $courier): void
    {
        //
    }

    /**
     * Handle the Courier "deleted" event.
     */
    public function deleted(Courier $courier): void
    {
        //
    }

    /**
     * Handle the Courier "restored" event.
     */
    public function restored(Courier $courier): void
    {
        //
    }

    /**
     * Handle the Courier "force deleted" event.
     */
    public function forceDeleted(Courier $courier): void
    {
        //
    }
}
