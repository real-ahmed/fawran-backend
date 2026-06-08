<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\DTOs\Vendor\Product\UpdateVendorItemStatusDTO;
use App\DTOs\Vendor\Product\VendorItemDataDTO;
use App\Http\Controllers\Api\V1\Vendor\Concerns\ResolvesVendorContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\Product\StoreVendorItemRequest;
use App\Http\Requests\V1\Vendor\Product\UpdateVendorItemRequest;
use App\Http\Requests\V1\Vendor\Product\UpdateVendorItemStatusRequest;
use App\Http\Resources\V1\VendorItemResource;
use App\Models\Product\VendorItem;
use App\Services\Vendor\VendorItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorItemController extends Controller
{
    use ResolvesVendorContext;

    public function __construct(
        private readonly VendorItemService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = $this->service->getItems($this->vendor($request));

        return $this->paginatedResponse($items, VendorItemResource::collection($items->items()));
    }

    public function store(StoreVendorItemRequest $request): JsonResponse
    {
        $dto = VendorItemDataDTO::fromValidated($request->validated());

        $item = $this->service->createItem($this->vendor($request), $dto);

        return $this->successResponse(new VendorItemResource($item), __('messages.created_successfully'), 201);
    }

    public function show(Request $request, VendorItem $item): JsonResponse
    {
        $item = $this->service->getItem($item, $this->vendor($request));

        return $this->successResponse(new VendorItemResource($item));
    }

    public function update(UpdateVendorItemRequest $request, VendorItem $item): JsonResponse
    {
        $dto = VendorItemDataDTO::fromValidated($request->validated());
        $item = $this->service->updateItem($item, $this->vendor($request), $dto);

        return $this->successResponse(new VendorItemResource($item), __('messages.updated_successfully'));
    }

    public function updateStatus(UpdateVendorItemStatusRequest $request, VendorItem $item): JsonResponse
    {
        $dto = UpdateVendorItemStatusDTO::fromValidated($request->validated());
        $item = $this->service->updateStatus($item, $this->vendor($request), $dto);

        return $this->successResponse(new VendorItemResource($item), __('messages.updated_successfully'));
    }

    public function destroy(Request $request, VendorItem $item): JsonResponse
    {
        $this->service->deleteItem($item, $this->vendor($request));

        return $this->successResponse(null, __('messages.deleted_successfully'));
    }
}
