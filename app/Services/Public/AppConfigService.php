<?php

namespace App\Services\Public;

use App\Enums\AdminPermission;
use App\Enums\VendorPermission;
use App\Models\Platform\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;

class AppConfigService
{
    public function publicConfig(): array
    {
        return SystemSetting::cachedPublicConfig();
    }

    public function adminPermissions(): array
    {
        return Cache::flexible('public.admin_permissions', [3600, 7200], function (): array {
            return $this->permissionsForGuard('api_admin', AdminPermission::class);
        });
    }

    public function vendorPermissions(): array
    {
        return Cache::flexible('public.vendor_permissions', [3600, 7200], function (): array {
            return $this->permissionsForGuard('api_vendor', VendorPermission::class);
        });
    }

    public function enums(): array
    {
        return Cache::rememberForever('public.system_enums', function (): array {
            $enumPath = app_path('Enums');
            $files = File::allFiles($enumPath);
            $enumsList = [];

            foreach ($files as $file) {
                $filename = $file->getFilenameWithoutExtension();
                if (in_array($filename, ['AdminPermission', 'VendorPermission'])) {
                    continue;
                }

                $class = 'App\\Enums\\'.$filename;
                if (enum_exists($class)) {
                    $enumsList[$filename] = collect($class::cases())->mapWithKeys(function ($case) {
                        return [$case->name => $case->value ?? $case->name];
                    })->all();
                }
            }

            return $enumsList;
        });
    }

    /**
     * @param  class-string  $enumClass
     */
    private function permissionsForGuard(string $guard, string $enumClass): array
    {
        return Permission::where('guard_name', $guard)
            ->orderBy('id')
            ->get()
            ->map(function ($permission) use ($enumClass) {
                $enumKey = $enumClass::tryFrom($permission->name)?->name
                    ?? strtoupper(str_replace(' ', '_', $permission->name));

                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'key' => $enumKey,
                ];
            })
            ->groupBy(function ($permission) {
                $parts = explode(' ', $permission['name'], 2);

                return $parts[1] ?? 'general';
            })
            ->map->values()
            ->toArray();
    }
}
