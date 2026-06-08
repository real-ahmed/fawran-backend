<?php

namespace App\Services\Courier;

use App\Jobs\Courier\SyncCourierLocationToDatabaseJob;
use App\Models\Courier\Courier;
use App\Models\Courier\CourierLocation;
use App\Models\Order\Delivery;
use App\Models\User;
use App\Services\Admin\OrderNotificationService;
use Illuminate\Support\Facades\Redis;

class CourierLocationService
{
    private const REDIS_KEY = 'courier_locations';

    public function updateUserLocation(User $user, float $lat, float $lng): void
    {
        $courier = $user->courier;

        abort_if(! $courier, 404, __('messages.courier_profile_not_found'));

        $this->updateLocation($courier->id, $lat, $lng);
    }

    /**
     * Update courier location in Redis and dispatch async DB sync.
     */
    public function updateLocation(int $courierId, float $lat, float $lng): void
    {
        // Add or update the courier's coordinates in the Redis Geospatial index
        Redis::geoadd(self::REDIS_KEY, $lng, $lat, $courierId);

        // Dispatch a background job to sync the location to the persistent DB
        SyncCourierLocationToDatabaseJob::dispatch($courierId, $lat, $lng);

        // Fetch active delivery
        $activeDelivery = Delivery::with('order')->where('courier_id', $courierId)
            ->whereIn('status', ['heading_to_vendors', 'out_for_delivery'])
            ->first();

        if ($activeDelivery) {
            $elapsedMinutes = (int) now()->diffInMinutes($activeDelivery->created_at);
            $initialEta = $activeDelivery->estimated_minutes ?? 0;
            $remainingEta = max(0, $initialEta - $elapsedMinutes);

            // Temporarily load location relation on a dummy courier to pass to notification service
            $courier = new Courier;
            $courier->id = $courierId;
            $courier->setRelation('location', new CourierLocation([
                'latitude' => $lat,
                'longitude' => $lng,
            ]));

            app(OrderNotificationService::class)->notifyCourierLocationChange(
                $activeDelivery->order,
                $courier,
                null,
                $remainingEta
            );
        }
    }

    /**
     * Get nearby courier IDs within a given radius.
     *
     * @return array<int> Array of courier IDs
     */
    public function getNearbyCouriers(float $lat, float $lng, float $radiusKm): array
    {
        // georadius returns an array of members (courier IDs)
        $courierIds = Redis::georadius(self::REDIS_KEY, $lng, $lat, $radiusKm, 'km');

        return array_map('intval', $courierIds);
    }
}
