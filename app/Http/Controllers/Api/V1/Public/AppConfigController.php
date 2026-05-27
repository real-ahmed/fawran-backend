<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Enums\AdminPermission;
use App\Enums\VendorPermission;
use App\Http\Controllers\Controller;
use App\Models\Platform\SystemSetting;
use Illuminate\Http\JsonResponse;
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
        $keys = [
            'app_name',
            'app_icon',
            'app_logo',
            'app_logo_white',
            'favicon',
            'currency',
        ];

        $settings = SystemSetting::whereIn('key', $keys)
            ->get()
            ->pluck('value', 'key');

        return $this->successResponse($settings);
    }

    /**
     * Get All Permissions
     *
     * Retrieves all system permissions mapped with a key for the frontend.
     */
    public function adminPermissions(): JsonResponse
    {
        $permissions = Permission::where('guard_name', 'api_admin')->get()->map(function ($permission) {
            $enumKey = AdminPermission::tryFrom($permission->name)?->name
                ?? strtoupper(str_replace(' ', '_', $permission->name));

            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'key' => $enumKey,
            ];
        })->groupBy(function ($permission) {
            $parts = explode(' ', $permission['name'], 2);

            return $parts[1] ?? 'general';
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
        $permissions = Permission::where('guard_name', 'api_vendor')->get()->map(function ($permission) {
            $enumKey = VendorPermission::tryFrom($permission->name)?->name
                ?? strtoupper(str_replace(' ', '_', $permission->name));

            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'key' => $enumKey,
            ];
        })->groupBy(function ($permission) {
            $parts = explode(' ', $permission['name'], 2);

            return $parts[1] ?? 'general';
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
        $enums = [
            'VendorType' => collect(\App\Enums\VendorType::cases())->mapWithKeys(function ($case) {
                return [$case->name => $case->value];
            }),
            'VendorStatus' => collect(\App\Enums\VendorStatus::cases())->mapWithKeys(function ($case) {
                return [$case->name => $case->value];
            }),
        ];

        return $this->successResponse($enums);
    }
}
