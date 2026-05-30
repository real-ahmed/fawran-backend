<?php

namespace App\Http\Controllers\Api\V1\Courier;

use App\Http\Controllers\Controller;
use App\Services\Courier\CourierLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(private CourierLocationService $locationService) {}

    /**
     * Update courier's real-time location.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $courier = $request->user()->courier;

        abort_if(! $courier, 404, __('messages.courier_profile_not_found'));

        $this->locationService->updateLocation(
            $courier->id,
            (float) $validated['latitude'],
            (float) $validated['longitude']
        );

        return response()->json([
            'message' => __('messages.location_updated_successfully'),
        ]);
    }
}
