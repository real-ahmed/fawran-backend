<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'monthly_price',
        'commission_percentage',
        'features',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'monthly_price' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
    ];
}
