<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Api\V1\Vendor\Concerns\ResolvesVendorContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\PurchaseOrder\StorePurchaseOrderRequest;
use App\Http\Requests\V1\Vendor\PurchaseOrder\UpdatePurchaseOrderStatusRequest;
use App\Http\Resources\V1\Vendor\PurchaseOrderResource;
use App\Models\Inventory\PurchaseOrder;
use App\Services\Vendor\VendorPurchaseOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PurchaseOrderController extends Controller
{
    use ResolvesVendorContext;

    public function __construct(
        private readonly VendorPurchaseOrderService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $purchaseOrders = $this->service->listPurchaseOrders($this->vendorId($request));

        return $this->paginatedResponse($purchaseOrders, PurchaseOrderResource::collection($purchaseOrders->items()));
    }

    public function show(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder = $this->service->getPurchaseOrder($purchaseOrder, $this->vendorId($request));

        return $this->successResponse(new PurchaseOrderResource($purchaseOrder));
    }

    public function store(StorePurchaseOrderRequest $request): JsonResponse
    {
        $purchaseOrder = $this->service->createPurchaseOrder($this->vendorId($request), $request->validated());

        return $this->successResponse(new PurchaseOrderResource($purchaseOrder), __('messages.created_successfully'), 201);
    }

    public function updateStatus(UpdatePurchaseOrderStatusRequest $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        try {
            $purchaseOrder = $this->service->updatePurchaseOrderStatus($purchaseOrder, $this->vendorId($request), $request->validated('status'));

            return $this->successResponse(new PurchaseOrderResource($purchaseOrder), __('messages.updated_successfully'));
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }
}
