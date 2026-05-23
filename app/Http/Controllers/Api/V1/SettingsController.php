<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Profile\UpdateSettingsRequest;

/**
 * @group Shared - Profile Settings
 *
 * APIs for managing cross-platform user settings (Admin, Customer, Vendor, Courier).
 */
class SettingsController extends Controller
{
    public function update(UpdateSettingsRequest $request)
    {
        $user = auth()->user() ?? auth('api_admin')->user();

        if (!$user || !method_exists($user, 'setSetting')) {
            return response()->json([
                'success' => false,
                'message' => __('messages.settings_cannot_be_updated'),
            ], 403);
        }

        foreach ($request->validated('settings') as $setting) {
            $user->setSetting($setting['key'], $setting['value']);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.settings_updated_successfully'),
            'data' => $user->settings()->get(['key', 'value']),
        ]);
    }
}
