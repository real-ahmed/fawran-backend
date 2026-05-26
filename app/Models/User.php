<?php

namespace App\Models;

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
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasLocalePreference, JWTSubject
{
    use HasFactory, HasRoles, HasSettings, Notifiable;

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
}
