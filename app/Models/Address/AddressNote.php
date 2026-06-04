<?php

namespace App\Models\Address;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressNote extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'address_id';

    public $incrementing = false;

    protected $fillable = [
        'address_id',
        'notes',
    ];

    public function address(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }
}
