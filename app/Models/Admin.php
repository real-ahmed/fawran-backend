<?php

namespace App\Models;

use App\Models\Payment\PayoutExecution;
use App\Models\Payment\SettlementExecution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admin extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'password' => 'hashed',
            'created_at' => 'datetime',
        ];
    }

    public function settlementExecutions(): HasMany
    {
        return $this->hasMany(SettlementExecution::class);
    }

    public function payoutExecutions(): HasMany
    {
        return $this->hasMany(PayoutExecution::class);
    }
}
