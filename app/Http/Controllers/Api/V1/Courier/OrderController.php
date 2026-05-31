<?php

namespace App\Http\Controllers\Api\V1\Courier;

use App\DTOs\Courier\Order\CourierAcceptOrderDTO;
use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Services\Courier\CourierOrderService;
use Exception;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(private CourierOrderService $courierOrderService) {}

    /**
     * Accept an order delivery request.
     */
    public function accept(\Illuminate\Http\Request $request, Order $order): JsonResponse
    {
        $dto = CourierAcceptOrderDTO::fromRequest($request->user()->courier->id, $order->id);
        $updatedOrder = $this->courierOrderService->acceptOrder($dto);

        return response()->json([
            'message' => __('messages.order_accepted'),
            'data' => [
                'order_id' => $updatedOrder->id,
                'status' => $updatedOrder->status->value,
            ],
        ]);
    }
}
