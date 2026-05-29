<?php

namespace App\Services\Geo;

use App\DTOs\Geo\DeliveryZone\DeliveryZoneDataDTO;
use App\DTOs\Geo\DeliveryZone\DeliveryZoneFilterDTO;
use App\Models\Geo\DeliveryZone;
use App\Traits\Paginatable;
use Illuminate\Support\Facades\DB;

class DeliveryZoneService
{
    use Paginatable;

    /**
     * Get paginated delivery zones with optional filters.
     */
    public function getZones(DeliveryZoneFilterDTO $filters)
    {
        return DeliveryZone::query()
            ->when($filters->search, fn ($q) => $q->searchIdentity($filters->search))
            ->when($filters->is_active !== null, fn ($q) => $q->where('is_active', $filters->is_active))
            ->withPolygonGeoJson()
            ->newest()
            ->cursorPaginate($this->getPerPageLimit());
    }

    /**
     * Get a single delivery zone by ID with GeoJSON.
     */
    public function getZoneById(int $id): DeliveryZone
    {
        return DeliveryZone::query()
            ->withPolygonGeoJson()
            ->findOrFail($id);
    }

    /**
     * Create a new Delivery Zone.
     */
    public function createZone(DeliveryZoneDataDTO $dto): DeliveryZone
    {
        $polygonWkt = $this->formatCoordinatesToWkt($dto->coordinates);

        $zone = new DeliveryZone;
        $zone->name = $dto->name;
        $zone->is_active = $dto->is_active ?? true;
        $zone->polygon = DB::raw("ST_GeomFromText('{$polygonWkt}')");
        $zone->save();

        return $this->getZoneById($zone->id);
    }

    /**
     * Update an existing Delivery Zone.
     */
    public function updateZone(DeliveryZone $zone, DeliveryZoneDataDTO $dto): DeliveryZone
    {
        if ($dto->name !== null) {
            $zone->name = $dto->name;
        }

        if ($dto->is_active !== null) {
            $zone->is_active = $dto->is_active;
        }

        if ($dto->coordinates !== null) {
            $polygonWkt = $this->formatCoordinatesToWkt($dto->coordinates);
            $zone->polygon = DB::raw("ST_GeomFromText('{$polygonWkt}')");
        }

        $zone->save();

        return $this->getZoneById($zone->id);
    }

    public function deleteZone(DeliveryZone $zone): void
    {
        $zone->delete();
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
