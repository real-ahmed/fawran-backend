<?php

namespace App\Services\Courier;

use App\Models\Courier\Courier;
use Illuminate\Database\Eloquent\Collection;

class CourierService
{
    /**
     * Get online couriers within a specific radius of a coordinate.
     *
     * @return Collection<Courier>
     */
    public function getNearbyOnlineCouriers(float $lat, float $lng, float $radiusKm): Collection
    {
        $radiusMeters = $radiusKm * 1000;

        return Courier::query()
            ->where('is_online', true)
            ->where('cod_blocked', false) // Exclude blocked couriers
            ->whereHas('location', function ($query) use ($lat, $lng, $radiusMeters) {
                // ST_Distance_Sphere calculates the distance in meters between two points
                $query->whereRaw('ST_Distance_Sphere(
                    POINT(longitude, latitude),
                    POINT(?, ?)
                ) <= ?', [$lng, $lat, $radiusMeters]);
            })
            ->with('location')
            ->get();
    }
}
