<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Api\V1\Vendor\Concerns\ResolvesVendorContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\Inventory\AdjustInventoryRequest;
use App\Http\Requests\V1\Vendor\Inventory\UpdateInventoryRequest;
use App\Http\Resources\V1\Vendor\InventoryResource;
use App\Models\Product\VendorItemInventory;
use App\Services\Vendor\VendorInventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    use ResolvesVendorContext;

    public function __construct(
        private readonly VendorInventoryService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $inventory = $this->service->listInventory($this->vendorId($request));

        return $this->paginatedResponse($inventory, InventoryResource::collection($inventory->items()));
    }

    public function update(UpdateInventoryRequest $request, VendorItemInventory $inventory): JsonResponse
    {
        $inventory = $this->service->updateInventory($inventory, $this->vendorId($request), $request->validated());

        return $this->successResponse(new InventoryResource($inventory), __('messages.updated_successfully'));
    }

    public function adjust(AdjustInventoryRequest $request): JsonResponse
    {
        $inventory = $this->service->adjustInventory(
            $this->vendorId($request),
            $request->vendor_item_id,
            $request->adjustment,
            $request->reason
        );

        return $this->successResponse(new InventoryResource($inventory), __('messages.updated_successfully'));
    }
}
