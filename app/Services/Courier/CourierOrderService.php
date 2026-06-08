<?php

namespace App\Services\Courier;

use App\DTOs\Courier\Order\CourierAcceptOrderDTO;
use App\Enums\OrderStatus;
use App\Models\Courier\Courier;
use App\Models\Order\Order;
use App\Models\User;
use App\Notifications\Courier\OrderNoLongerAvailableNotification;
use App\Services\OrderService;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CourierOrderService
{
    public function __construct(private OrderService $orderService) {}

    /**
     * Accept a delivery request.
     *
     * @throws Exception
     */
    public function acceptOrderForUser(User $user, Order $order): Order
    {
        $courier = $user->courier;

        abort_if(! $courier, 404, __('messages.courier_profile_not_found'));

        return $this->acceptOrder(CourierAcceptOrderDTO::fromRequest($courier->id, $order->id));
    }

    /**
     * @throws Exception
     */
    public function acceptOrder(CourierAcceptOrderDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            // Lock the order row to prevent race conditions (multiple couriers accepting at the same time)
            $order = Order::where('id', $dto->orderId)->lockForUpdate()->firstOrFail();

            if (! in_array($order->status->value, [OrderStatus::Pending->value, OrderStatus::Processing->value])) {
                throw new Exception(__('messages.order_no_longer_available'));
            }

            // Assign the courier (this will update statuses, calculate fees, and create the Delivery record)
            $this->orderService->assignCourier($order, $dto->courierId);

            // Notify other couriers that the order is taken
            $this->notifyOthersOrderIsTaken($dto->orderId, $dto->courierId);

            return $order->refresh();
        });
    }

    private function notifyOthersOrderIsTaken(int $orderId, int $acceptedCourierId): void
    {
        $cacheKey = "order:{$orderId}:notified_couriers";

        /** @var array|null $notifiedCourierIds */
        $notifiedCourierIds = Cache::get($cacheKey);

        if (is_array($notifiedCourierIds)) {
            // Remove the courier who accepted it
            $othersToNotify = array_diff($notifiedCourierIds, [$acceptedCourierId]);

            if (! empty($othersToNotify)) {
                $otherCouriers = Courier::whereIn('id', $othersToNotify)->with('user')->get();
                $order = Order::find($orderId);

                if ($order) {
                    $notification = new OrderNoLongerAvailableNotification($order);
                    foreach ($otherCouriers as $otherCourier) {
                        $otherCourier->user->notify($notification);
                    }
                }
            }

            // Clean up cache
            Cache::forget($cacheKey);
        }
    }
}
