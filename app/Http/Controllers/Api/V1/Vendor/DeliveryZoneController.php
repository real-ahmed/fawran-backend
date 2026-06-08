<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Api\V1\Vendor\Concerns\ResolvesVendorContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\DeliveryZone\StoreDeliveryZoneRequest;
use App\Http\Requests\V1\Vendor\DeliveryZone\UpdateDeliveryZoneRequest;
use App\Http\Resources\V1\Vendor\DeliveryZoneResource;
use App\Models\Geo\VendorDeliveryZone;
use App\Services\Vendor\VendorDeliveryZoneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryZoneController extends Controller
{
    use ResolvesVendorContext;

    public function __construct(
        private readonly VendorDeliveryZoneService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $zones = $this->service->listZones($this->vendorId($request));

        return $this->paginatedResponse($zones, DeliveryZoneResource::collection($zones->items()));
    }

    public function store(StoreDeliveryZoneRequest $request): JsonResponse
    {
        $zone = $this->service->createZone($this->vendorId($request), $request->validated());

        return $this->successResponse(new DeliveryZoneResource($zone), __('messages.created_successfully'), 201);
    }

    public function update(UpdateDeliveryZoneRequest $request, VendorDeliveryZone $zone): JsonResponse
    {
        $zone = $this->service->updateZone($zone, $this->vendorId($request), $request->validated());

        return $this->successResponse(new DeliveryZoneResource($zone), __('messages.updated_successfully'));
    }

    public function destroy(Request $request, VendorDeliveryZone $zone): JsonResponse
    {
        $this->service->deleteZone($zone, $this->vendorId($request));

        return $this->successResponse(null, __('messages.deleted_successfully'));
    }
}
