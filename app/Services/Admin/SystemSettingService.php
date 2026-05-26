<?php

namespace App\Services\Admin;

use App\Models\Platform\SystemSetting;

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
        foreach ($settings as $setting) {
            SystemSetting::where('key', $setting['key'])->update([
                'value' => $setting['value'],
                'updated_at' => now(),
            ]);
        }
    }
}
