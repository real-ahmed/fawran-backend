<?php

namespace App\Models\Geo;

use App\Enums\HotZoneIntensity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HotZone extends Model
{
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
}
