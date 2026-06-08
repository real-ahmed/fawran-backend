<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\General\Settings\UserSettingsDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Profile\UpdateSettingsRequest;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;

/**
 * @group Shared - Profile Settings
 *
 * APIs for managing cross-platform user settings (Admin, Customer, Vendor, Courier).
 */
class SettingsController extends Controller
{
    public function __construct(protected SettingsService $settingsService) {}

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $dto = UserSettingsDTO::fromRequest($request);

        return $this->successResponse(
            $this->settingsService->updateCurrentUserSettings($dto),
            __('messages.settings_updated_successfully')
        );
    }
}
