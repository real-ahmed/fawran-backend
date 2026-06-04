<?php

namespace App\Models;

use App\Builders\AdminBuilder;
use App\Models\Courier\CourierApproval;
use App\Models\Geo\DeliveryZone;
use App\Models\Payment\PayoutExecution;
use App\Models\Payment\SettlementExecution;
use App\Traits\HasSettings;
use App\Traits\HasSystemTimezoneDates;
use App\Traits\Scopes\AdminZoneScope;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable implements HasLocalePreference, JWTSubject
{
    use AdminZoneScope, HasFactory, HasRoles, HasSettings, HasSystemTimezoneDates, Notifiable;

    public $timestamps = false;

    protected $guard_name = 'api_admin';

    protected $with = ['roles', 'permissions'];

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

    protected function applyZoneFilter(Builder $query, array $zoneIds): void
    {
        $query->whereHas('deliveryZones', fn (Builder $query): Builder => $query->whereIn('delivery_zones.id', $zoneIds));
    }

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

    public function isSuperAdmin(): bool
    {
        return $this->id === 1 || $this->roles->contains(
            fn (Role $role): bool => $role->isSuperAdmin()
        );
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function newEloquentBuilder($query): AdminBuilder
    {
        return new AdminBuilder($query);
    }

    public function preferredLocale(): string
    {
        return $this->getSetting('locale') ?? config('app.locale', 'en');
    }

    public function receivesBroadcastNotificationsOn(): string
    {
        return 'admin.'.$this->id;
    }
}
