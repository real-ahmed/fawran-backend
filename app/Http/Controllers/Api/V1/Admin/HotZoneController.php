<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HotZone\IndexHotZoneRequest;
use App\Http\Requests\Admin\HotZone\StoreHotZoneRequest;
use App\Http\Requests\Admin\HotZone\UpdateHotZoneRequest;
use App\Http\Resources\Admin\HotZoneResource;
use App\Models\Geo\HotZone;
use App\Services\Admin\HotZoneService;

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
    public function index(IndexHotZoneRequest $request)
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
    public function store(StoreHotZoneRequest $request)
    {
        $hotZone = $this->hotZoneService->createHotZone($request->validated());

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
    public function update(UpdateHotZoneRequest $request, HotZone $hotZone)
    {
        $hotZone = $this->hotZoneService->updateHotZone($hotZone, $request->validated());

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
