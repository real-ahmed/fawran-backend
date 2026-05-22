<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;

class PlatformWallet extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'total_revenue',
        'current_balance',
    ];

    protected $attributes = [
        'total_revenue' => 0.00,
        'current_balance' => 0.00,
    ];

    protected function casts(): array
    {
        return [
            'total_revenue' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'updated_at' => 'datetime',
        ];
    }
}
