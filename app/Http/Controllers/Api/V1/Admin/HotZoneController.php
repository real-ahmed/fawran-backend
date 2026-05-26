<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\HotZoneResource;
use App\Models\Geo\HotZone;
use App\Services\Admin\HotZoneService;
use Illuminate\Http\Request;

/**
 * @group Admin - Hot Zones
 *
 * APIs for managing delivery hot zones for dynamic pricing and surge control.
 */
class HotZoneController extends Controller
{
    public function __construct(protected HotZoneService $hotZoneService) {}

    /**
     * List Hot Zones
     *
     * Get a paginated list of all hot zones with optional filters.
     *
     * @queryParam is_active boolean Filter by active status. Example: 1
     * @queryParam intensity string Filter by intensity (low, medium, high). Example: high
     */
    public function index(Request $request)
    {
        return HotZoneResource::collection($this->hotZoneService->listHotZones($request));
    }

    /**
     * Create Hot Zone
     *
     * Create a new manual hot zone with optional name.
     *
     * @bodyParam center_latitude numeric required Center latitude. Example: 30.0444
     * @bodyParam center_longitude numeric required Center longitude. Example: 31.2357
     * @bodyParam radius_meters int required Radius in meters. Example: 5000
     * @bodyParam intensity string required Intensity level (low, medium, high). Example: high
     * @bodyParam name object optional Localized name for manual hot zones. Example: {"en": "Downtown Cairo"}
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'center_latitude' => 'required|numeric|between:-90,90',
            'center_longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:100',
            'intensity' => 'required|string|in:low,medium,high',
            'is_active' => 'sometimes|boolean',
            'starts_at' => 'sometimes|date',
            'name' => 'sometimes|array',
        ]);

        $hotZone = $this->hotZoneService->createHotZone($validated);

        return $this->successResponse(
            new HotZoneResource($hotZone),
            __('messages.created_successfully'),
            201
        );
    }

    /**
     * Get Hot Zone Details
     *
     * Retrieve a specific hot zone with its manual/auto details.
     */
    public function show(HotZone $hotZone)
    {
        return $this->successResponse(
            new HotZoneResource($hotZone->load(['manualHotZone', 'autoHotZone']))
        );
    }

    /**
     * Update Hot Zone
     *
     * Modify an existing hot zone and its extension tables.
     */
    public function update(Request $request, HotZone $hotZone)
    {
        $validated = $request->validate([
            'center_latitude' => 'sometimes|numeric|between:-90,90',
            'center_longitude' => 'sometimes|numeric|between:-180,180',
            'radius_meters' => 'sometimes|integer|min:100',
            'intensity' => 'sometimes|string|in:low,medium,high',
            'is_active' => 'sometimes|boolean',
            'starts_at' => 'sometimes|date',
            'name' => 'nullable|array',
        ]);

        $hotZone = $this->hotZoneService->updateHotZone($hotZone, $validated);

        return $this->successResponse(
            new HotZoneResource($hotZone),
            __('messages.updated_successfully')
        );
    }

    /**
     * Delete Hot Zone
     *
     * Remove a hot zone and its extension tables.
     */
    public function destroy(HotZone $hotZone)
    {
        $this->hotZoneService->deleteHotZone($hotZone);

        return $this->successResponse(null, __('messages.deleted_successfully'));
    }
}
