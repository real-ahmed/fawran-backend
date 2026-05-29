<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\General\Settings\UserSettingsDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Profile\UpdateSettingsRequest;
use App\Services\SettingsService;

/**
 * @group Shared - Profile Settings
 *
 * APIs for managing cross-platform user settings (Admin, Customer, Vendor, Courier).
 */
class SettingsController extends Controller
{
    public function __construct(protected SettingsService $settingsService) {}

    public function update(UpdateSettingsRequest $request)
    {
        $dto = UserSettingsDTO::fromRequest($request);

        return response()->json([
            'success' => true,
            'message' => __('messages.settings_updated_successfully'),
            'data' => $this->settingsService->updateCurrentUserSettings($dto),
        ]);
    }
}
