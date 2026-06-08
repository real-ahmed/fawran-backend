<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\DTOs\Admin\Order\OrderFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Order\AssignCourierRequest;
use App\Http\Requests\V1\Admin\Order\IndexOrderRequest;
use App\Http\Requests\V1\Admin\Order\UpdateOrderStatusRequest;
use App\Http\Resources\V1\Admin\OrderResource;
use App\Models\Order\Order;
use App\Services\Admin\OrderService as AdminOrderService;
use Illuminate\Http\Request;

/**
 * @group Admin - Orders
 *
 * APIs for managing platform orders, viewing details, and handling cancellations.
 */
class OrderController extends Controller
{
    public function __construct(protected AdminOrderService $adminOrderService) {}

    /**
     * List Orders
     *
     * Get a paginated list of all platform orders with optional filters.
     *
     * @queryParam status string Filter by status (pending, processing, out_for_delivery, delivered, cancelled). Example: pending
     * @queryParam order_type string Filter by type (delivery, pickup, in_store). Example: delivery
     * @queryParam date_from string Filter orders from this date. Example: 2026-01-01
     * @queryParam date_to string Filter orders until this date. Example: 2026-12-31
     * @queryParam customer_id int Filter by customer ID. Example: 1
     * @queryParam vendor_id int Filter by vendor ID. Example: 1
     */
    public function index(IndexOrderRequest $request)
    {
        $dto = OrderFilterDTO::fromRequest($request);
        $orders = $this->adminOrderService->listOrders($dto);

        return $this->paginatedResponse($orders, OrderResource::collection($orders->items()));
    }

    /**
     * Get Order Details
     *
     * Retrieve a specific order with all nested details: customer, sub-orders, items, delivery, payments.
     */
    public function show(Order $order)
    {
        return $this->successResponse(
            new OrderResource($this->adminOrderService->getOrder($order))
        );
    }

    /**
     * Cancel Order
     *
     * Cancel an order and all its sub-orders.
     */
    public function cancel(Order $order)
    {
        $this->adminOrderService->cancelOrder($order);

        return $this->successResponse(null, __('messages.order_cancelled_successfully'));
    }

    public function statusCounts(Request $request)
    {
        $dto = OrderFilterDTO::fromRequest($request);

        return $this->successResponse($this->adminOrderService->getOrderStatusCounts($dto));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        try {
            $this->adminOrderService->updateStatus($order, $request->validated('status'));

            return $this->successResponse(null, __('messages.order_status_updated_successfully'));
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function assignCourier(AssignCourierRequest $request, Order $order)
    {
        $this->adminOrderService->assignCourier($order, $request->validated('courier_id'));

        return $this->successResponse(null, __('messages.courier_assigned_successfully'));
    }

    public function deliveryPath(Order $order)
    {
        return $this->successResponse($this->adminOrderService->deliveryPath($order));
    }
}
