<?php

namespace App\Services;

use App\DTOs\General\Settings\UserSettingsDTO;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SettingsService
{
    /**
     * @return Collection<int, object>
     */
    public function updateCurrentUserSettings(UserSettingsDTO $dto): Collection
    {
        return $this->updateFor(auth()->user() ?? auth('api_admin')->user(), $dto);
    }

    /**
     * @return Collection<int, object>
     */
    public function updateFor(?Authenticatable $user, UserSettingsDTO $dto): Collection
    {
        if (! $user || ! method_exists($user, 'setSetting')) {
            throw new HttpException(403, __('messages.settings_cannot_be_updated'));
        }

        foreach ($dto->settings as $setting) {
            $user->setSetting($setting['key'], $setting['value']);
        }

        return $user->settings()->get(['key', 'value']);
    }
}
