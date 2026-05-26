<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Vendor\IndexVendorRequest;
use App\Http\Requests\V1\Admin\Vendor\StoreVendorRequest;
use App\Http\Requests\V1\Admin\Vendor\UpdateVendorRequest;
use App\Http\Resources\V1\VendorResource;
use App\Models\Vendor\Vendor;
use App\Services\Admin\VendorService;

/**
 * @group Admin - Vendors Management
 *
 * APIs for managing store vendors (Restaurants, Pharmacies, Groceries).
 */
class VendorController extends Controller
{
    public function __construct(protected VendorService $vendorService) {}

    /**
     * List Vendors
     *
     * Get a paginated list of all vendors with optional filters.
     *
     * @queryParam type string Filter by vendor type (restaurant, grocery, pharmacy). Example: restaurant
     * @queryParam is_active boolean Filter by active status (1 or 0). Example: 1
     */
    public function index(IndexVendorRequest $request)
    {
        return VendorResource::collection($this->vendorService->listVendors($request));
    }

    /**
     * Create Vendor
     *
     * Create a new vendor in the system.
     */
    public function store(StoreVendorRequest $request)
    {
        $vendor = $this->vendorService->createVendor($request->validated());

        return $this->successResponse(
            new VendorResource($vendor),
            __('messages.created_successfully'),
            201
        );
    }

    /**
     * Get Vendor
     *
     * Retrieve details of a specific vendor.
     */
    public function show(Vendor $vendor)
    {
        return $this->successResponse(new VendorResource($vendor));
    }

    /**
     * Update Vendor
     *
     * Update an existing vendor's details or status.
     */
    public function update(UpdateVendorRequest $request, Vendor $vendor)
    {
        $vendor = $this->vendorService->updateVendor($vendor, $request->validated());

        return $this->successResponse(
            new VendorResource($vendor),
            __('messages.updated_successfully')
        );
    }

    /**
     * Delete Vendor
     *
     * Remove a vendor from the system.
     */
    public function destroy(Vendor $vendor)
    {
        $this->vendorService->deleteVendor($vendor);

        return $this->successResponse(null, __('messages.deleted_successfully'));
    }
}
