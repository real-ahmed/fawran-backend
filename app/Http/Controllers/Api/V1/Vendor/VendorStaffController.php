<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Api\V1\Vendor\Concerns\ResolvesVendorContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\Staff\StoreVendorStaffRequest;
use App\Http\Requests\V1\Vendor\Staff\UpdateVendorStaffRequest;
use App\Http\Resources\V1\VendorStaffResource;
use App\Models\Vendor\VendorStaff;
use App\Services\Vendor\VendorStaffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorStaffController extends Controller
{
    use ResolvesVendorContext;

    public function __construct(
        private readonly VendorStaffService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $staff = $this->service->listStaff($this->vendorId($request));

        return $this->paginatedResponse($staff, VendorStaffResource::collection($staff->items()));
    }

    public function show(Request $request, VendorStaff $staff): JsonResponse
    {
        $staff = $this->service->getStaff($staff, $this->vendorId($request));

        return $this->successResponse(new VendorStaffResource($staff));
    }

    public function store(StoreVendorStaffRequest $request): JsonResponse
    {
        $staff = $this->service->createStaff($this->vendorId($request), $request->validated());

        return $this->successResponse(new VendorStaffResource($staff), __('messages.vendor_staff_created'), 201);
    }

    public function update(UpdateVendorStaffRequest $request, VendorStaff $staff): JsonResponse
    {
        $staff = $this->service->updateStaff($staff, $this->vendorId($request), $request->validated());

        return $this->successResponse(new VendorStaffResource($staff), __('messages.vendor_staff_updated'));
    }

    public function destroy(Request $request, VendorStaff $staff): JsonResponse
    {
        $this->service->deleteStaff($staff, $this->vendorId($request));

        return $this->successResponse(null, __('messages.vendor_staff_deleted'));
    }
}
