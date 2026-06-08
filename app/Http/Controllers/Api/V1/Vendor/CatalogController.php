<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\DTOs\Vendor\Catalog\MasterProduct\MasterProductSubmissionDTO;
use App\Http\Controllers\Api\V1\Vendor\Concerns\ResolvesVendorContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\Product\StoreMasterProductRequest;
use App\Services\Vendor\Catalog\MasterProductService as MasterProductSubmissionService;
use App\Services\Vendor\VendorCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    use ResolvesVendorContext;

    public function __construct(
        private readonly VendorCatalogService $service,
        private readonly MasterProductSubmissionService $masterProductSubmissionService
    ) {}

    public function categories(Request $request): JsonResponse
    {
        $categories = $this->service->getCategories();

        return $this->paginatedResponse($categories, $categories->items());
    }

    public function brands(Request $request): JsonResponse
    {
        $brands = $this->service->getBrands();

        return $this->paginatedResponse($brands, $brands->items());
    }

    public function masterProducts(Request $request): JsonResponse
    {
        $products = $this->service->getMasterProducts($request->only(['category_id', 'search']));

        return $this->paginatedResponse($products, $products->items());
    }

    public function storeMasterProduct(StoreMasterProductRequest $request): JsonResponse
    {
        $dto = MasterProductSubmissionDTO::fromRequest($request);
        $masterProduct = $this->masterProductSubmissionService->submit($dto, $this->vendor($request));

        return $this->successResponse($masterProduct, __('messages.created_successfully'), 201);
    }
}
