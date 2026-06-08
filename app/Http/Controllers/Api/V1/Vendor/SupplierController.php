<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Api\V1\Vendor\Concerns\ResolvesVendorContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\Supplier\StoreSupplierRequest;
use App\Http\Requests\V1\Vendor\Supplier\UpdateSupplierRequest;
use App\Http\Resources\V1\Vendor\SupplierResource;
use App\Models\Inventory\Supplier;
use App\Services\Vendor\VendorSupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use ResolvesVendorContext;

    public function __construct(
        private readonly VendorSupplierService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $suppliers = $this->service->listSuppliers($this->vendorId($request));

        return $this->paginatedResponse($suppliers, SupplierResource::collection($suppliers->items()));
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = $this->service->createSupplier($this->vendorId($request), $request->validated());

        return $this->successResponse(new SupplierResource($supplier), __('messages.created_successfully'), 201);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier = $this->service->updateSupplier($supplier, $this->vendorId($request), $request->validated());

        return $this->successResponse(new SupplierResource($supplier), __('messages.updated_successfully'));
    }

    public function destroy(Request $request, Supplier $supplier): JsonResponse
    {
        $this->service->deleteSupplier($supplier, $this->vendorId($request));

        return $this->successResponse(null, __('messages.deleted_successfully'));
    }
}
