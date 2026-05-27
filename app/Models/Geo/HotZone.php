<?php

namespace App\Models\Geo;

use App\Builders\HotZoneBuilder;
use App\Enums\HotZoneIntensity;
use App\Traits\Scopes\AdminZoneScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class HotZone extends Model
{
    use AdminZoneScope;

    protected function applyZoneFilter(Builder $query, array $zoneIds): void
    {
        $query->whereExists(function ($sub) use ($zoneIds) {
            $sub->select(DB::raw(1))
                ->from('delivery_zones')
                ->whereIn('id', $zoneIds)
                ->whereRaw('ST_Contains(polygon, POINT(hot_zones.center_longitude, hot_zones.center_latitude))');
        });
    }

    public $timestamps = false;

    protected $fillable = [
        'center_latitude',
        'center_longitude',
        'radius_meters',
        'intensity',
        'is_active',
        'starts_at',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'center_latitude' => 'decimal:8',
            'center_longitude' => 'decimal:8',
            'intensity' => HotZoneIntensity::class,
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
        ];
    }

    public function manualHotZone(): HasOne
    {
        return $this->hasOne(ManualHotZone::class);
    }

    public function autoHotZone(): HasOne
    {
        return $this->hasOne(AutoHotZone::class);
    }

    public function newEloquentBuilder($query): HotZoneBuilder
    {
        return new HotZoneBuilder($query);
    }
}
