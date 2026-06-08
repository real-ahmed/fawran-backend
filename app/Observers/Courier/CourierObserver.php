<?php

namespace App\Observers\Courier;

use App\Enums\AdminPermission;
use App\Events\CourierApplicationSubmitted;
use App\Models\Admin;
use App\Models\Courier\Courier;
use App\Notifications\Admin\CourierApplicationSubmittedNotification;
use App\Services\AdminNotificationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class CourierObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the Courier "created" event.
     */
    public function created(Courier $courier): void
    {
        $adminsToNotify = collect();

        $superAdmins = Admin::query()->superAdmins()->get();

        $adminsToNotify = $adminsToNotify->merge($superAdmins);

        if ($courier->location) {
            $lon = $courier->location->longitude;
            $lat = $courier->location->latitude;

            $zoneAdmins = Admin::whereHas('deliveryZones', function ($q) use ($lon, $lat) {
                $q->whereRaw("ST_Contains(delivery_zones.polygon, ST_GeomFromText(CONCAT('POINT(', ?, ' ', ?, ')')))", [$lon, $lat]);
            })->get();

            $adminsToNotify = $adminsToNotify->merge($zoneAdmins);
        }

        $uniqueAdmins = $adminsToNotify->unique('id');

        foreach ($uniqueAdmins as $admin) {
            event(new CourierApplicationSubmitted($courier, $admin));
        }

        app(AdminNotificationService::class)->notifyAdminsWithPermission(
            AdminPermission::APPROVE_COURIERS->value,
            new CourierApplicationSubmittedNotification($courier->user->name ?? 'Unknown'),
            $uniqueAdmins
        );
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
