<?php

namespace App\Events;

use App\Models\Admin;
use App\Models\Order\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public Admin $admin,
        public string $fromStatus,
        public string $toStatus
    ) {}

    public function broadcastWith(): array
    {
        $locale = $this->admin->preferredLocale();

        return [
            'order_id' => $this->order->id,
            'from_status' => $this->fromStatus,
            'to_status' => $this->toStatus,
            'message' => __('messages.order_status_updated', [
                'id' => $this->order->id,
                'status' => __('enums.order_status.'.$this->toStatus, [], $locale),
            ], $locale),
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.'.$this->admin->id),
        ];
    }
}
