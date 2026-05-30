<?php

namespace App\Services\Courier;

use App\Models\Courier\Courier;
use Illuminate\Database\Eloquent\Collection;

class CourierService
{
    public function __construct(private CourierLocationService $locationService) {}

    /**
     * Get online couriers within a specific radius of a coordinate.
     *
     * @return Collection<Courier>
     */
    public function getNearbyOnlineCouriers(float $lat, float $lng, float $radiusKm): Collection
    {
        // 1. Get nearby courier IDs from Redis
        $courierIds = $this->locationService->getNearbyCouriers($lat, $lng, $radiusKm);

        if (empty($courierIds)) {
            return new Collection;
        }

        // 2. Fetch those couriers from MySQL
        return Courier::query()
            ->whereIn('id', $courierIds)
            ->where('is_online', true)
            ->where('cod_blocked', false) // Exclude blocked couriers
            ->with('location') // Eager load historical DB location if needed elsewhere
            ->get();
    }
}
