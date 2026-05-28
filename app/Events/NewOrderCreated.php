<?php

namespace App\Events;

use App\Models\Admin;
use App\Models\Order\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order, public Admin $admin) {}

    public function broadcastWith(): array
    {
        $locale = $this->admin->preferredLocale();
        $name = $this->order->customer?->customer?->name ?? __('messages.unknown', [], $locale);

        return [
            'order_id' => $this->order->id,
            'message' => __('messages.new_order_created', ['name' => $name, 'id' => $this->order->id], $locale),
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.'.$this->admin->id),
        ];
    }
}
