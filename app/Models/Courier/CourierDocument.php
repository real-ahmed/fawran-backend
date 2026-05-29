<?php

namespace App\Models\Courier;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CourierDocument extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'courier_id';

    public $incrementing = false;

    protected $fillable = [
        'courier_id',
        'criminal_record_file',
        'contract_number',
    ];

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    /**
     * Get the full URL for the criminal record file.
     */
    protected function criminalRecordFile(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if ($value && ! str_starts_with($value, 'http')) {
                    return asset(Storage::disk('public')->url($value));
                }

                return $value;
            }
        );
    }
}
