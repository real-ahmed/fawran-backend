<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SettingsService
{
    /**
     * @param  array<int, array{key: string, value: mixed}>  $settings
     * @return Collection<int, object>
     */
    public function updateCurrentUserSettings(array $settings): Collection
    {
        return $this->updateFor(auth()->user() ?? auth('api_admin')->user(), $settings);
    }

    /**
     * @param  array<int, array{key: string, value: mixed}>  $settings
     * @return Collection<int, object>
     */
    public function updateFor(?Authenticatable $user, array $settings): Collection
    {
        if (! $user || ! method_exists($user, 'setSetting')) {
            throw new HttpException(403, __('messages.settings_cannot_be_updated'));
        }

        foreach ($settings as $setting) {
            $user->setSetting($setting['key'], $setting['value']);
        }

        return $user->settings()->get(['key', 'value']);
    }
}
