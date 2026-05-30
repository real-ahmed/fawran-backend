<?php

namespace App\Observers\Order;

use App\Jobs\Courier\BroadcastOrderToCouriersJob;
use App\Models\Order\Order;
use App\Services\Admin\OrderNotificationService;

class OrderObserver
{
    public $afterCommit = true;

    public function __construct(private OrderNotificationService $notificationService) {}

    public function created(Order $order): void
    {
        $this->notificationService->notifyNewOrder($order);

        // Dispatch job to broadcast to nearby couriers
        BroadcastOrderToCouriersJob::dispatch($order);
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            $oldStatus = $order->getOriginal('status')->value ?? $order->getOriginal('status');
            $newStatus = $order->status->value ?? $order->status;

            $this->notificationService->notifyStatusChange($order, $oldStatus, $newStatus);
        }
    }
}
