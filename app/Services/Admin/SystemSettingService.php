<?php

namespace App\Services\Admin;

use App\Models\Platform\SystemSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
            $value = $setting['value'];

            if ($value instanceof UploadedFile) {
                // Uses the default disk configured in filesystems.php (local, s3, gcs, etc.)
                $path = $value->store('system_settings');
                $value = Storage::url($path);
            }

            SystemSetting::where('key', $setting['key'])->update([
                'value' => $value,
                'updated_at' => now(),
            ]);
        }
    }
}
