<?php

namespace App\Models\Geo;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoHotZone extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'hot_zone_id';

    public $incrementing = false;

    protected $fillable = [
        'hot_zone_id',
        'order_count',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function hotZone(): BelongsTo
    {
        return $this->belongsTo(HotZone::class);
    }
}
