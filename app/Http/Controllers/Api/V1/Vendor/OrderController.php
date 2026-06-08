<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\DTOs\Vendor\Order\UpdateSubOrderStatusDTO;
use App\Http\Controllers\Api\V1\Vendor\Concerns\ResolvesVendorContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\Order\IndexSubOrderRequest;
use App\Http\Requests\V1\Vendor\Order\UpdateSubOrderStatusRequest;
use App\Http\Resources\V1\Vendor\SubOrderResource;
use App\Models\Order\SubOrder;
use App\Services\Vendor\VendorOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class OrderController extends Controller
{
    use ResolvesVendorContext;

    public function __construct(
        private readonly VendorOrderService $service
    ) {}

    public function index(IndexSubOrderRequest $request): JsonResponse
    {
        $orders = $this->service->listOrders($this->vendorId($request), $request->validated());

        return $this->paginatedResponse($orders, SubOrderResource::collection($orders->items()));
    }

    public function statusCounts(Request $request): JsonResponse
    {
        $counts = $this->service->getOrderCounts($this->vendorId($request));

        return $this->successResponse($counts);
    }

    public function show(Request $request, SubOrder $subOrder): JsonResponse
    {
        $subOrder = $this->service->getOrder($subOrder, $this->vendorId($request));

        return $this->successResponse(new SubOrderResource($subOrder));
    }

    public function updateStatus(UpdateSubOrderStatusRequest $request, SubOrder $subOrder): JsonResponse
    {
        $dto = UpdateSubOrderStatusDTO::fromValidated($request->validated(), $subOrder->id, $this->vendorId($request));

        try {
            $updatedOrder = $this->service->updateSubOrderStatus($dto);

            return $this->successResponse(new SubOrderResource($updatedOrder), __('messages.updated_successfully'));
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }
}
