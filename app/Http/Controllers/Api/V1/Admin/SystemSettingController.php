<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\SystemSettingResource;
use App\Services\Admin\SystemSettingService;
use Illuminate\Http\Request;

/**
 * @group Admin - System Settings
 *
 * APIs for managing global platform configuration settings.
 */
class SystemSettingController extends Controller
{
    public function __construct(protected SystemSettingService $systemSettingService) {}

    /**
     * List System Settings
     *
     * Get all system settings, optionally filtered by group.
     *
     * @queryParam group string Filter by settings group. Example: commissions
     */
    public function index(Request $request)
    {
        $settings = $this->systemSettingService->getSettings($request->query('group'));

        return SystemSettingResource::collection($settings);
    }

    /**
     * Update System Settings
     *
     * Batch update system settings by key-value pairs.
     *
     * @bodyParam settings array required Array of settings to update. Example: [{"key": "default_commission", "value": "15"}]
     * @bodyParam settings.*.key string required The setting key. Example: default_commission
     * @bodyParam settings.*.value string required The new value. Example: 15
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array|min:1',
            'settings.*.key' => 'required|string|exists:system_settings,key',
            'settings.*.value' => 'required|string',
        ]);

        $this->systemSettingService->updateSettings($validated['settings']);

        return $this->successResponse(null, __('messages.settings_updated_successfully'));
    }
}
