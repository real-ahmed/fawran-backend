<?php

namespace App\Traits;

use App\Models\UserSetting;
use Illuminate\Database\Eloquent\Relations\MorphMany;

use Illuminate\Support\Facades\Cache;

trait HasSettings
{
    /**
     * Get all of the model's settings.
     */
    public function settings(): MorphMany
    {
        return $this->morphMany(UserSetting::class, 'settingable');
    }

    /**
     * Get a specific setting value.
     */
    public function getSetting(string $key, $default = null)
    {
        $cacheKey = "settings_{$this->getMorphClass()}_{$this->getKey()}_{$key}";

        return Cache::rememberForever($cacheKey, function () use ($key, $default) {
            $setting = $this->settings()->where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a specific setting value.
     */
    public function setSetting(string $key, $value): void
    {
        $this->settings()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        $cacheKey = "settings_{$this->getMorphClass()}_{$this->getKey()}_{$key}";
        Cache::put($cacheKey, $value);
    }
}
