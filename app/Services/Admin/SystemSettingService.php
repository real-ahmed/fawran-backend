<?php

namespace App\Services\Admin;

use App\Models\Platform\SystemSetting;
use Illuminate\Http\UploadedFile;

class SystemSettingService
{
    public function getSettings(?string $group = null)
    {
        $query = SystemSetting::query();

        if ($group) {
            $query->where('group', $group);
        }

        return $query->orderBy('group')->orderBy('key')->get();
    }

    /**
     * Batch update settings.
     *
     * @param  array<int, array{key: string, value: string}>  $settings
     */
    public function updateSettings(array $settings): void
    {
        $updatedKeys = [];

        foreach ($settings as $setting) {
            $value = $setting['value'];

            if ($value instanceof UploadedFile) {
                // Store on public disk and save raw path
                $path = $value->store('system_settings', 'public');
                $value = $path;
            }

            SystemSetting::where('key', $setting['key'])->update([
                'value' => $value,
                'updated_at' => now(),
            ]);

            $updatedKeys[] = $setting['key'];
        }

        SystemSetting::flushCachedValues($updatedKeys);
    }
}
