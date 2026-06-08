<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\DTOs\Vendor\Product\ProductOptionDataDTO;
use App\Http\Controllers\Api\V1\Vendor\Concerns\ResolvesVendorContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\Product\StoreProductOptionRequest;
use App\Http\Requests\V1\Vendor\Product\UpdateProductOptionRequest;
use App\Http\Resources\V1\ProductOptionResource;
use App\Models\Product\ProductOption;
use App\Models\Product\VendorItem;
use App\Services\Vendor\ProductOptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductOptionController extends Controller
{
    use ResolvesVendorContext;

    public function __construct(
        private readonly ProductOptionService $service
    ) {}

    public function index(Request $request, VendorItem $item): JsonResponse
    {
        $options = $this->service->getOptions($item, $this->vendorId($request));

        return $this->successResponse(ProductOptionResource::collection($options));
    }

    public function store(StoreProductOptionRequest $request, VendorItem $item): JsonResponse
    {
        $dto = ProductOptionDataDTO::fromValidated($request->validated());
        $option = $this->service->createOption($item, $this->vendorId($request), $dto);

        return $this->successResponse(new ProductOptionResource($option), __('messages.created_successfully'), 201);
    }

    public function show(Request $request, VendorItem $item, ProductOption $option): JsonResponse
    {
        $option = $this->service->getOption($item, $option, $this->vendorId($request));

        return $this->successResponse(new ProductOptionResource($option));
    }

    public function update(UpdateProductOptionRequest $request, VendorItem $item, ProductOption $option): JsonResponse
    {
        $dto = ProductOptionDataDTO::fromValidated($request->validated());
        $option = $this->service->updateOption($item, $option, $this->vendorId($request), $dto);

        return $this->successResponse(new ProductOptionResource($option), __('messages.updated_successfully'));
    }

    public function destroy(Request $request, VendorItem $item, ProductOption $option): JsonResponse
    {
        $this->service->deleteOption($item, $option, $this->vendorId($request));

        return $this->successResponse(null, __('messages.deleted_successfully'));
    }
}
