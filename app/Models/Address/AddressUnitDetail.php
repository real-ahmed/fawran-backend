<?php

namespace App\Models\Address;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressUnitDetail extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'address_id';

    public $incrementing = false;

    protected $fillable = [
        'address_id',
        'floor_number',
        'apartment_number',
    ];

    public function address(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }
}
