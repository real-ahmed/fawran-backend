<?php

namespace App\Models;

use App\Builders\UserBuilder;
use App\Models\Address\UserAddress;
use App\Models\Auth\LocalAccount;
use App\Models\Auth\SocialAccount;
use App\Models\Courier\Courier;
use App\Models\Media\Favorite;
use App\Models\Media\Rating;
use App\Models\Media\SavedItem;
use App\Models\Payment\PayoutRequest;
use App\Models\Payment\Wallet;
use App\Models\Vendor\VendorStaff;
use App\Traits\HasSettings;
use App\Traits\HasSystemTimezoneDates;
use App\Traits\Scopes\AdminZoneScope;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasLocalePreference, JWTSubject
{
    use AdminZoneScope, HasFactory, HasRoles, HasSettings, HasSystemTimezoneDates, Notifiable;

    protected function applyZoneFilter(Builder $query, array $zoneIds): void
    {
        $query->where(function (Builder $query) use ($zoneIds): void {
            $query->whereExists(function ($sub) use ($zoneIds): void {
                $sub->selectRaw('1')
                    ->from('order_customers')
                    ->join('order_deliveries', 'order_deliveries.order_id', '=', 'order_customers.order_id')
                    ->whereColumn('order_customers.customer_id', 'users.id')
                    ->whereIn('order_deliveries.delivery_zone_id', $zoneIds)
                    ->limit(1);
            })->orWhereExists(function ($sub) use ($zoneIds): void {
                $sub->selectRaw('1')
                    ->from('user_addresses')
                    ->join('delivery_zones', function ($join) use ($zoneIds): void {
                        $this->joinDeliveryZonesContainingPoint($join, $zoneIds, 'user_addresses.longitude', 'user_addresses.latitude');
                    })
                    ->whereColumn('user_addresses.user_id', 'users.id')
                    ->limit(1);
            })->orWhereExists(function ($sub) use ($zoneIds): void {
                $sub->selectRaw('1')
                    ->from('vendors')
                    ->join('vendor_delivery_zones', 'vendor_delivery_zones.vendor_id', '=', 'vendors.id')
                    ->whereColumn('vendors.owner_id', 'users.id')
                    ->whereIn('vendor_delivery_zones.delivery_zone_id', $zoneIds)
                    ->limit(1);
            });

            $this->whereCourierLocationInAdminZones($query, $zoneIds, 'users.id', 'couriers.user_id', 'or');
        });
    }

    protected $fillable = [
        'name',
        'email',
        'phone',
        'is_active',
    ];

    protected $with = ['roles', 'permissions'];

    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function localAccount(): HasOne
    {
        return $this->hasOne(LocalAccount::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function courier(): HasOne
    {
        return $this->hasOne(Courier::class);
    }

    public function vendorStaff(): HasMany
    {
        return $this->hasMany(VendorStaff::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function savedItems(): HasMany
    {
        return $this->hasMany(SavedItem::class);
    }

    public function payoutRequests(): HasMany
    {
        return $this->hasMany(PayoutRequest::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Route notifications for the FCM channel.
     */
    public function routeNotificationForFcm($notification): ?string
    {
        // Assuming there will be a user_devices table or an fcm_token column in the future
        return $this->fcm_token ?? null;
    }

    /**
     * Route notifications for the SMS channel.
     */
    public function routeNotificationForSms($notification): ?string
    {
        return $this->phone;
    }

    /**
     * Get the user's preferred locale.
     */
    public function preferredLocale(): string
    {
        return $this->getSetting('locale', 'en');
    }

    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }
}
