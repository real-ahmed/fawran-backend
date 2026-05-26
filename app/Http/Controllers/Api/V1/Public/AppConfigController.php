<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Platform\SystemSetting;
use Illuminate\Http\JsonResponse;

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
            'favicon',
        ];

        $settings = SystemSetting::whereIn('key', $keys)
            ->get()
            ->pluck('value', 'key');

        return $this->successResponse($settings);
    }
}
