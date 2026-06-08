<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\DTOs\Customer\Order\PlaceOrderDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Customer\Order\PlaceOrderRequest;
use App\Services\Customer\OrderService;
use Exception;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    /**
     * Place a new order.
     */
    public function store(PlaceOrderRequest $request): JsonResponse
    {
        try {
            $dto = PlaceOrderDTO::fromValidated($request->validated(), $request->user()->id);
            $order = $this->orderService->placeOrder($dto);

            return $this->successResponse(
                [
                    'order_id' => $order->id,
                    'status' => $order->status->value,
                    'total_products' => $order->total_products,
                    'total_delivery_fee' => $order->orderDelivery?->total_delivery_fee ?? '0.00',
                ],
                __('messages.order_placed_successfully'),
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }
}
