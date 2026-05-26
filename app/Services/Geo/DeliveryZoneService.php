<?php

namespace App\Services\Geo;

use App\Models\Geo\DeliveryZone;
use App\Traits\Paginatable;
use Illuminate\Support\Facades\DB;

class DeliveryZoneService
{
    use Paginatable;

    /**
     * Get paginated delivery zones with optional filters.
     */
    public function getZones(array $filters)
    {
        $perPage = $this->getPerPageLimit($filters['per_page'] ?? null);

        return DeliveryZone::filter($filters)
            ->select('*', DB::raw('ST_AsGeoJSON(polygon) as polygon_geojson'))
            ->orderByDesc('id')
            ->cursorPaginate($perPage);
    }

    /**
     * Get a single delivery zone by ID with GeoJSON.
     */
    public function getZoneById(int $id): DeliveryZone
    {
        return DeliveryZone::select('*', DB::raw('ST_AsGeoJSON(polygon) as polygon_geojson'))
            ->findOrFail($id);
    }

    /**
     * Create a new Delivery Zone.
     *
     * @param  array  $data  Expected format: ['name' => [...], 'coordinates' => [['lat' => X, 'lng' => Y], ...], 'is_active' => true]
     */
    public function createZone(array $data): DeliveryZone
    {
        $polygonWkt = $this->formatCoordinatesToWkt($data['coordinates']);

        $zone = new DeliveryZone;
        $zone->name = $data['name'];
        $zone->is_active = $data['is_active'] ?? true;
        $zone->polygon = DB::raw("ST_GeomFromText('{$polygonWkt}')");
        $zone->save();

        return $this->getZoneById($zone->id);
    }

    /**
     * Update an existing Delivery Zone.
     */
    public function updateZone(DeliveryZone $zone, array $data): DeliveryZone
    {
        if (isset($data['name'])) {
            $zone->name = $data['name'];
        }

        if (isset($data['is_active'])) {
            $zone->is_active = $data['is_active'];
        }

        if (isset($data['coordinates'])) {
            $polygonWkt = $this->formatCoordinatesToWkt($data['coordinates']);
            $zone->polygon = DB::raw("ST_GeomFromText('{$polygonWkt}')");
        }

        $zone->save();

        return $this->getZoneById($zone->id);
    }

    /**
     * Convert an array of ['lat' => x, 'lng' => y] coordinates into a WKT Polygon string.
     * Automatically ensures the polygon is mathematically closed.
     */
    protected function formatCoordinatesToWkt(array $coordinates): string
    {
        $first = reset($coordinates);
        $last = end($coordinates);

        // A valid MySQL POLYGON must be closed (the first and last points must be identical)
        if ($first['lat'] !== $last['lat'] || $first['lng'] !== $last['lng']) {
            $coordinates[] = $first;
        }

        $points = array_map(function ($coord) {
            // MySQL spatial coordinates are formatted as 'Longitude Latitude'
            return "{$coord['lng']} {$coord['lat']}";
        }, $coordinates);

        $pointsString = implode(',', $points);

        return "POLYGON(({$pointsString}))";
    }
}
