<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\DeliveryZone\IndexDeliveryZoneRequest;
use App\Http\Requests\V1\Admin\DeliveryZone\VendorDeliveryZoneRequest;
use App\Http\Requests\V1\Admin\DeliveryZone\UpdateDeliveryZoneRequest;
use App\Http\Resources\V1\DeliveryZoneResource;
use App\Models\Geo\DeliveryZone;
use App\Services\Geo\DeliveryZoneService;
use Illuminate\Support\Facades\DB;

class DeliveryZoneController extends Controller
{
    protected DeliveryZoneService $deliveryZoneService;

    public function __construct(DeliveryZoneService $deliveryZoneService)
    {
        $this->deliveryZoneService = $deliveryZoneService;
    }

    public function index(IndexDeliveryZoneRequest $request)
    {
        $zones = $this->deliveryZoneService->getZones($request->validated());
        
        return DeliveryZoneResource::collection($zones)->additional([
            'success' => true,
            'message' => 'Delivery zones retrieved successfully',
        ]);
    }

    public function store(VendorDeliveryZoneRequest $request)
    {
        $zone = $this->deliveryZoneService->createZone($request->validated());

        return $this->successResponse(new DeliveryZoneResource($zone), 'Delivery zone created successfully', 201);
    }

    public function show($id)
    {
        $zone = $this->deliveryZoneService->getZoneById($id);

        return $this->successResponse(new DeliveryZoneResource($zone), 'Delivery zone retrieved successfully');
    }

    public function update(UpdateDeliveryZoneRequest $request, DeliveryZone $deliveryZone)
    {
        $zone = $this->deliveryZoneService->updateZone($deliveryZone, $request->validated());

        return $this->successResponse(new DeliveryZoneResource($zone), 'Delivery zone updated successfully');
    }

    public function destroy(DeliveryZone $deliveryZone)
    {
        $deliveryZone->delete();

        return $this->successResponse(null, 'Delivery zone deleted successfully');
    }
}
