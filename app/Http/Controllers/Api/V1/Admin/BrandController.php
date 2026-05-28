<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Brand\IndexBrandRequest;
use App\Http\Requests\Admin\Brand\StoreBrandRequest;
use App\Http\Requests\Admin\Brand\UpdateBrandRequest;
use App\Http\Resources\Admin\BrandResource;
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
        return BrandResource::collection($this->brandService->listBrands($request));
    }

    /**
     * Create Brand
     *
     * Store a newly created brand in the catalog.
     */
    public function store(StoreBrandRequest $request)
    {
        $brand = $this->brandService->createBrand($request->validated());

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
        return $this->successResponse(new BrandResource($brand));
    }

    /**
     * Update Brand
     *
     * Modify an existing brand's details.
     */
    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $brand = $this->brandService->updateBrand($brand, $request->validated());

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
