<?php

namespace App\Http\Controllers\Api\V1\Courier;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Services\Courier\CourierOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private CourierOrderService $courierOrderService) {}

    /**
     * Accept an order delivery request.
     */
    public function accept(Request $request, Order $order): JsonResponse
    {
        $updatedOrder = $this->courierOrderService->acceptOrderForUser($request->user(), $order);

        return $this->successResponse(
            [
                'order_id' => $updatedOrder->id,
                'status' => $updatedOrder->status->value,
            ],
            __('messages.order_accepted')
        );
    }
}
