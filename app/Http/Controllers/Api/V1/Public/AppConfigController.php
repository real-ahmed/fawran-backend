<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Enums\AdminPermission;
use App\Enums\VendorPermission;
use App\Enums\VendorStatus;
use App\Enums\VendorType;
use App\Http\Controllers\Controller;
use App\Models\Platform\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;

/**
 * @group Public - Application Configuration
 *
 * Unauthenticated APIs for frontend applications to fetch required startup config.
 */
class AppConfigController extends Controller
{
    /**
     * Get App Config
     *
     * Retrieves essential public branding and configuration settings.
     */
    public function index(): JsonResponse
    {
        return $this->successResponse(SystemSetting::cachedPublicConfig());
    }

    /**
     * Get All Permissions
     *
     * Retrieves all system permissions mapped with a key for the frontend.
     */
    public function adminPermissions(): JsonResponse
    {
        $permissions = Cache::flexible('public.admin_permissions', [3600, 7200], function (): array {
            return Permission::where('guard_name', 'api_admin')
                ->orderBy('id')
                ->get()
                ->map(function ($permission) {
                    $enumKey = AdminPermission::tryFrom($permission->name)?->name
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
        });

        return $this->successResponse($permissions);
    }

    /**
     * Get All Vendor Permissions
     *
     * Retrieves all vendor system permissions mapped with a key for the frontend.
     */
    public function vendorPermissions(): JsonResponse
    {
        $permissions = Cache::flexible('public.vendor_permissions', [3600, 7200], function (): array {
            return Permission::where('guard_name', 'api_vendor')
                ->orderBy('id')
                ->get()
                ->map(function ($permission) {
                    $enumKey = VendorPermission::tryFrom($permission->name)?->name
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
        });

        return $this->successResponse($permissions);
    }

    /**
     * Get System Enums
     *
     * Retrieves system enums and their translations for frontend synchronization.
     */
    public function enums(): JsonResponse
    {
        $enums = Cache::rememberForever('public.admin_enums', fn (): array => [
            'VendorType' => collect(VendorType::cases())->mapWithKeys(function ($case) {
                return [$case->name => $case->value];
            })->all(),
            'VendorStatus' => collect(VendorStatus::cases())->mapWithKeys(function ($case) {
                return [$case->name => $case->value];
            })->all(),
        ]);

        return $this->successResponse($enums);
    }
}
