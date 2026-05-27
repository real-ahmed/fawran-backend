<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class DeliveryZoneBuilder extends Builder
{
    /**
     * Scope a query to only include active delivery zones.
     */
    public function active(): self
    {
        return $this->where('is_active', true);
    }

    /**
     * Scope a query to find zones that contain a specific point (lat, lng).
     * MySQL uses (longitude, latitude) order for ST_GeomFromText.
     */
    public function containsPoint(float $latitude, float $longitude): self
    {
        $point = "POINT({$longitude} {$latitude})";

        return $this->whereRaw('ST_Contains(polygon, ST_GeomFromText(?))', [$point]);
    }

    /**
     * Apply an array of filters (search, is_active).
     */
    public function filter(array $filters): self
    {
        $this->when(isset($filters['is_active']), function ($query) use ($filters) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        });

        $this->when(! empty($filters['search']), function ($query) use ($filters) {
            $query->search($filters['search']);
        });

        return $this;
    }

    /**
     * Search by name (which is a JSON column).
     */
    public function search(string $term): self
    {
        // Since name is JSON {"ar": "...", "en": "..."}, we use JSON path searching
        return $this->where(function ($query) use ($term) {
            $query->where('name->ar', 'LIKE', "%{$term}%")
                ->orWhere('name->en', 'LIKE', "%{$term}%");
        });
    }
}
