<?php

namespace App\Models\Vendor;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorWorkingHour extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'vendor_id',
        'day_of_week',
        'open_time',
        'close_time',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
