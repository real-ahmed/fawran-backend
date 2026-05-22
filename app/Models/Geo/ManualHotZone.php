<?php

namespace App\Models\Geo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManualHotZone extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'hot_zone_id';

    public $incrementing = false;

    protected $fillable = [
        'hot_zone_id',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
        ];
    }

    public function hotZone(): BelongsTo
    {
        return $this->belongsTo(HotZone::class);
    }
}
