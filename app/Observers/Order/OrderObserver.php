<?php

namespace App\Observers\Order;

use App\Enums\OrderType;
use App\Events\OrderConfirmed;
use App\Events\OrderDelivered;
use App\Jobs\Courier\BroadcastOrderToCouriersJob;
use App\Models\Order\Order;
use App\Services\Admin\OrderNotificationService;
use BackedEnum;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class OrderObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private OrderNotificationService $notificationService) {}

    public function created(Order $order): void
    {
        $this->notificationService->notifyNewOrder($order);

        // Only broadcast to couriers for delivery orders
        if ($order->order_type === OrderType::Delivery) {
            // Dispatch job to broadcast to nearby couriers
            BroadcastOrderToCouriersJob::dispatch($order);
        }
    }

    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $oldStatus = $this->statusValue($order->getOriginal('status'));
        $newStatus = $this->statusValue($order->status);

        $this->notificationService->notifyStatusChange($order, $oldStatus, $newStatus);

        if ($newStatus === 'processing') {
            OrderConfirmed::dispatch($order);
        }

        if ($newStatus === 'delivered') {
            $order->loadMissing('delivery');

            if ($order->delivery) {
                OrderDelivered::dispatch($order, $order->delivery);
            }
        }
    }

    private function statusValue(mixed $status): string
    {
        if ($status instanceof BackedEnum) {
            return (string) $status->value;
        }

        return (string) $status;
    }
}
