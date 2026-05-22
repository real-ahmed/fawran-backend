<?php

namespace App\Models\Courier;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
