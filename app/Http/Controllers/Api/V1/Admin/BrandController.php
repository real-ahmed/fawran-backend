<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\DTOs\Admin\Catalog\BrandDataDTO;
use App\DTOs\Admin\Catalog\BrandFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Brand\IndexBrandRequest;
use App\Http\Requests\V1\Admin\Brand\StoreBrandRequest;
use App\Http\Requests\V1\Admin\Brand\UpdateBrandRequest;
use App\Http\Resources\V1\Admin\BrandResource;
use App\Models\Catalog\Brand;
use App\Services\Admin\Catalog\BrandService;

/**
 * @group Admin - Brands
 *
 * APIs for managing product brands in the catalog.
 */
class BrandController extends Controller
{
    public function __construct(protected BrandService $brandService) {}

    /**
     * List Brands
     *
     * Get a paginated list of all brands.
     */
    public function index(IndexBrandRequest $request)
    {
        $dto = BrandFilterDTO::fromRequest($request);
        $brands = $this->brandService->listBrands($dto);

        return $this->paginatedResponse($brands, BrandResource::collection($brands->items()));
    }

    /**
     * Create Brand
     *
     * Store a newly created brand in the catalog.
     */
    public function store(StoreBrandRequest $request)
    {
        $dto = BrandDataDTO::fromRequest($request);
        $brand = $this->brandService->createBrand($dto);

        return $this->successResponse(
            new BrandResource($brand),
            'Brand created successfully',
            201
        );
    }

    /**
     * Get Brand Details
     *
     * Retrieve a specific brand by ID.
     */
    public function show(Brand $brand)
    {
        return $this->successResponse(new BrandResource($this->brandService->getBrand($brand)));
    }

    /**
     * Update Brand
     *
     * Modify an existing brand's details.
     */
    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $dto = BrandDataDTO::fromRequest($request);
        $brand = $this->brandService->updateBrand($brand, $dto);

        return $this->successResponse(
            new BrandResource($brand),
            'Brand updated successfully'
        );
    }

    /**
     * Delete Brand
     *
     * Remove the specified brand from the catalog.
     */
    public function destroy(Brand $brand)
    {
        $this->brandService->deleteBrand($brand);

        return $this->successResponse(null, 'Brand deleted successfully');
    }

    /**
     * Approve Brand
     *
     * Approve a vendor-submitted brand and notify the vendor.
     */
    public function approve(Brand $brand)
    {
        $this->brandService->approveBrand($brand);

        return $this->successResponse(null, __('messages.brand_approved_successfully'));
    }

    /**
     * Reject Brand
     *
     * Reject a vendor-submitted brand.
     */
    public function reject(Brand $brand)
    {
        $this->brandService->rejectBrand($brand);

        return $this->successResponse(null, __('messages.brand_rejected_successfully'));
    }
}
