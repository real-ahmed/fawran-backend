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

    public function updateSettings(array $settings): void
    {
        $updatedKeys = [];
        $mergedJsonKeys = ['app_logo', 'app_logo_white', 'app_icon', 'favicon'];
        $pendingMerges = [];

        foreach ($settings as $setting) {
            $key = $setting['key'];
            $value = $setting['value'];

            // Handle localized image uploads like app_logo_ar
            $isMergedKey = false;
            foreach ($mergedJsonKeys as $mergedKey) {
                if (preg_match('/^(' . $mergedKey . ')_(ar|en)$/', $key, $matches)) {
                    $baseKey = $matches[1];
                    $locale = $matches[2];
                    $isMergedKey = true;

                    if ($value instanceof UploadedFile) {
                        $path = $value->store('system_settings', 'public');
                        $value = $path;
                    }

                    if (!isset($pendingMerges[$baseKey])) {
                        $existing = SystemSetting::where('key', $baseKey)->first();
                        $existingJson = json_decode($existing?->value ?? '{"ar":"","en":""}', true);
                        if (!is_array($existingJson)) {
                            // Convert legacy single string to array
                            $existingJson = ['ar' => $existing?->value, 'en' => $existing?->value];
                        }
                        $pendingMerges[$baseKey] = [
                            'json' => $existingJson,
                            'group' => $existing?->group ?? 'general',
                        ];
                    }

                    $pendingMerges[$baseKey]['json'][$locale] = $value;
                    break;
                }
            }

            if ($isMergedKey) {
                continue;
            }

            if ($value instanceof UploadedFile) {
                // Store on public disk and save raw path
                $path = $value->store('system_settings', 'public');
                $value = $path;
            }

            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now(), 'group' => 'general'] // Using updateOrCreate to be safe
            );

            $updatedKeys[] = $key;
        }

        // Process pending merges
        foreach ($pendingMerges as $baseKey => $data) {
            SystemSetting::updateOrCreate(
                ['key' => $baseKey],
                ['value' => json_encode($data['json']), 'updated_at' => now(), 'group' => $data['group']]
            );
            $updatedKeys[] = $baseKey;
        }

        SystemSetting::flushCachedValues($updatedKeys);
    }
}
