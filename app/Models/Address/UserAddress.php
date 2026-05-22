<?php

namespace App\Models\Address;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserAddress extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'formatted_address',
        'building_number',
        'phone',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unitDetail(): HasOne
    {
        return $this->hasOne(AddressUnitDetail::class, 'address_id');
    }

    public function note(): HasOne
    {
        return $this->hasOne(AddressNote::class, 'address_id');
    }
}
