<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\DTOs\Admin\SystemSetting\SystemSettingDataDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\SystemSetting\IndexSystemSettingRequest;
use App\Http\Requests\V1\Admin\SystemSetting\UpdateSystemSettingRequest;
use App\Http\Resources\V1\Admin\SystemSettingResource;
use App\Services\Admin\SystemSettingService;

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
    public function index(IndexSystemSettingRequest $request)
    {
        $settings = $this->systemSettingService->getSettings($request->validated('group'));

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
    public function update(UpdateSystemSettingRequest $request)
    {
        $dto = SystemSettingDataDTO::fromRequest($request);
        $this->systemSettingService->updateSettings($dto);

        return $this->successResponse(null, __('messages.settings_updated_successfully'));
    }
}
