<?php

namespace App\Models;

use App\Models\Courier\CourierApproval;
use App\Models\Geo\DeliveryZone;
use App\Models\Payment\PayoutExecution;
use App\Models\Payment\SettlementExecution;
use App\Traits\HasSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable implements JWTSubject
{
    use HasFactory, HasRoles, HasSettings;

    public $timestamps = false;

    protected $guard_name = 'api_admin';

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

    public function deliveryZones(): BelongsToMany
    {
        return $this->belongsToMany(DeliveryZone::class, 'admin_delivery_zones');
    }

    public function courierApprovals(): HasMany
    {
        return $this->hasMany(CourierApproval::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
