<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Services\Public\AppConfigService;
use Illuminate\Http\JsonResponse;

/**
 * @group Public - Application Configuration
 *
 * Unauthenticated APIs for frontend applications to fetch required startup config.
 */
class AppConfigController extends Controller
{
    public function __construct(protected AppConfigService $appConfigService) {}

    /**
     * Get App Config
     *
     * Retrieves essential public branding and configuration settings.
     */
    public function index(): JsonResponse
    {
        return $this->successResponse($this->appConfigService->publicConfig());
    }

    /**
     * Get All Permissions
     *
     * Retrieves all system permissions mapped with a key for the frontend.
     */
    public function adminPermissions(): JsonResponse
    {
        return $this->successResponse($this->appConfigService->adminPermissions());
    }

    /**
     * Get All Vendor Permissions
     *
     * Retrieves all vendor system permissions mapped with a key for the frontend.
     */
    public function vendorPermissions(): JsonResponse
    {
        return $this->successResponse($this->appConfigService->vendorPermissions());
    }

    /**
     * Get System Enums
     *
     * Retrieves system enums and their translations for frontend synchronization.
     */
    public function enums(): JsonResponse
    {
        return $this->successResponse($this->appConfigService->enums());
    }
}
