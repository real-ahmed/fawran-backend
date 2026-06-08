<?php

namespace App\Http\Controllers\Api\V1\Courier;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Courier\UpdateLocationRequest;
use App\Services\Courier\CourierLocationService;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function __construct(private CourierLocationService $locationService) {}

    /**
     * Update courier's real-time location.
     */
    public function update(UpdateLocationRequest $request): JsonResponse
    {
        $this->locationService->updateUserLocation(
            $request->user(),
            (float) $request->validated('latitude'),
            (float) $request->validated('longitude')
        );

        return $this->successResponse(null, __('messages.location_updated_successfully'));
    }
}
