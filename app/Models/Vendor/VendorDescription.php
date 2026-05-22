<?php

namespace App\Models\Vendor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorDescription extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'vendor_id';

    public $incrementing = false;

    protected $fillable = [
        'vendor_id',
        'description',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
