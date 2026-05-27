<?php

namespace App\Models\Platform;

use App\Builders\SystemSettingBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SystemSetting extends Model
{
    public const PUBLIC_CONFIG_CACHE_KEY = 'system_settings.public_config';

    public const PUBLIC_CONFIG_KEYS = [
        'app_name',
        'app_icon',
        'app_logo',
        'app_logo_white',
        'favicon',
        'currency',
    ];

    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime',
        ];
    }

    public static function cachedValue(string $key, ?string $default = null): ?string
    {
        return Cache::memo()->remember(
            self::valueCacheKey($key),
            now()->addMinutes(30),
            fn (): ?string => self::query()->key($key)->first()?->value ?? $default
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function cachedPublicConfig(): array
    {
        return Cache::flexible(
            self::PUBLIC_CONFIG_CACHE_KEY,
            [300, 900],
            fn (): array => self::query()
                ->keys(self::PUBLIC_CONFIG_KEYS)
                ->get()
                ->pluck('value', 'key')
                ->all()
        );
    }

    /**
     * @param  iterable<int, string>  $keys
     */
    public static function flushCachedValues(iterable $keys): void
    {
        Cache::forget(self::PUBLIC_CONFIG_CACHE_KEY);

        foreach ($keys as $key) {
            Cache::forget(self::valueCacheKey($key));
        }
    }

    public static function valueCacheKey(string $key): string
    {
        return "system_settings.value.{$key}";
    }

    public function newEloquentBuilder($query): SystemSettingBuilder
    {
        return new SystemSettingBuilder($query);
    }

    protected function value(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $imageKeys = ['app_logo', 'app_logo_white', 'app_icon', 'favicon'];

                if (in_array($this->key, $imageKeys) && ! empty($value)) {
                    $decoded = json_decode($value, true);

                    if (is_array($decoded)) {
                        $processed = [];
                        foreach ($decoded as $lang => $path) {
                            if (! empty($path) && ! str_starts_with($path, 'http')) {
                                $cleanPath = preg_replace('/^\/?storage\//', '', $path);
                                $processed[$lang] = asset(Storage::disk('public')->url($cleanPath));
                            } else {
                                $processed[$lang] = $path;
                            }
                        }

                        return json_encode($processed);
                    } elseif (! str_starts_with($value, 'http')) {
                        // Legacy single string support
                        $cleanValue = preg_replace('/^\/?storage\//', '', $value);

                        return asset(Storage::disk('public')->url($cleanValue));
                    }
                }

                return $value;
            }
        );
    }
}
