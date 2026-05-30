<?php

namespace App\Services\Courier;

use App\Jobs\Courier\SyncCourierLocationToDatabaseJob;
use Illuminate\Support\Facades\Redis;

class CourierLocationService
{
    private const REDIS_KEY = 'courier_locations';

    /**
     * Update courier location in Redis and dispatch async DB sync.
     */
    public function updateLocation(int $courierId, float $lat, float $lng): void
    {
        // Add or update the courier's coordinates in the Redis Geospatial index
        Redis::geoadd(self::REDIS_KEY, $lng, $lat, $courierId);

        // Dispatch a background job to sync the location to the persistent DB
        SyncCourierLocationToDatabaseJob::dispatch($courierId, $lat, $lng);
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
