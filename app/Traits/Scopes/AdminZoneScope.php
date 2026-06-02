<?php

namespace App\Traits\Scopes;

use App\Models\Admin;
use App\Services\Admin\AdminZoneService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait AdminZoneScope
{
    /**
     * Scope a query to only include records relevant to the authenticated admin's zones.
     * Super Admins bypass this scope.
     */
    public function scopeForAdminZones(Builder $query): Builder
    {
        $adminZoneService = app(AdminZoneService::class);
        $admin = $adminZoneService->currentAdmin();

        if ($adminZoneService->shouldRestrict($admin)) {
            $zoneIds = $adminZoneService->zoneIdsFor($admin);

            if (empty($zoneIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $this->applyZoneFilter($query, $zoneIds);
            }
        }

        return $query;
    }

    public function ensureVisibleToAdminZones(): static
    {
        if (! $this->isVisibleToAdminZones()) {
            throw new NotFoundHttpException;
        }

        return $this;
    }

    public function isVisibleToAdminZones(?Admin $admin = null): bool
    {
        $adminZoneService = app(AdminZoneService::class);
        $admin ??= $adminZoneService->currentAdmin();

        if (! $adminZoneService->shouldRestrict($admin)) {
            return true;
        }

        $zoneIds = $adminZoneService->zoneIdsFor($admin);

        if (empty($zoneIds)) {
            return false;
        }

        $query = static::query()->whereKey($this->getKey());
        $this->applyZoneFilter($query, $zoneIds);

        return $query->exists();
    }

    /**
     * @param  list<int>  $zoneIds
     */
    protected function whereCourierLocationInAdminZones(
        Builder $query,
        array $zoneIds,
        string $modelColumn,
        string $courierColumn = 'couriers.id',
        string $boolean = 'and'
    ): void {
        $method = $boolean === 'or' ? 'orWhereExists' : 'whereExists';

        $query->{$method}(function (QueryBuilder $subQuery) use ($courierColumn, $modelColumn, $zoneIds): void {
            $subQuery->selectRaw('1')
                ->from('couriers')
                ->join('courier_locations', 'courier_locations.courier_id', '=', 'couriers.id')
                ->join('delivery_zones', function (JoinClause $join) use ($zoneIds): void {
                    $this->joinDeliveryZonesContainingPoint($join, $zoneIds, 'courier_locations.longitude', 'courier_locations.latitude');
                })
                ->whereColumn($courierColumn, $modelColumn)
                ->limit(1);
        });
    }

    /**
     * @param  list<int>  $zoneIds
     */
    protected function joinDeliveryZonesContainingPoint(
        JoinClause $join,
        array $zoneIds,
        string $longitudeColumn,
        string $latitudeColumn
    ): void {
        $join->whereIn('delivery_zones.id', $zoneIds)
            ->whereRaw("ST_Contains(delivery_zones.polygon, ST_GeomFromText(CONCAT('POINT(', {$longitudeColumn}, ' ', {$latitudeColumn}, ')')))");
    }

    /**
     * Apply the specific zone filter logic for the model.
     */
    abstract protected function applyZoneFilter(Builder $query, array $zoneIds): void;
}
