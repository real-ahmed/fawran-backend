<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor\Vendor;
use App\Http\Requests\V1\Admin\Vendor\StoreVendorRequest;
use App\Http\Requests\V1\Admin\Vendor\UpdateVendorRequest;
use App\Http\Resources\V1\VendorResource;
use Illuminate\Http\Request;

/**
 * @group Admin - Vendors Management
 *
 * APIs for managing store vendors (Restaurants, Pharmacies, Groceries).
 */
class VendorController extends Controller
{
    /**
     * List Vendors
     *
     * Get a paginated list of all vendors with optional filters.
     *
     * @queryParam type string Filter by vendor type (restaurant, grocery, pharmacy). Example: restaurant
     * @queryParam is_active boolean Filter by active status (1 or 0). Example: 1
     */
    public function index(Request $request)
    {
        $query = Vendor::query();

        if ($request->has('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->query('is_active'));
        }

        $vendors = $query->latest()->paginate($request->query('per_page', 15));

        return VendorResource::collection($vendors);
    }

    /**
     * Create Vendor
     *
     * Create a new vendor in the system.
     */
    public function store(StoreVendorRequest $request)
    {
        $vendor = Vendor::create($request->validated());

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
        $vendor->update($request->validated());

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
        $vendor->delete();

        return $this->successResponse(null, __('messages.deleted_successfully'));
    }
}
