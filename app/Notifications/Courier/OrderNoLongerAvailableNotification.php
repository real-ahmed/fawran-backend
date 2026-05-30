<?php

namespace App\Notifications\Courier;

use App\Broadcasting\FcmChannel;
use App\Models\Order\Order;
use App\Notifications\Concerns\QueuesNotificationDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class OrderNoLongerAvailableNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesNotificationDelivery;

    public function __construct(
        public Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['broadcast', FcmChannel::class]; // No need to save in DB, just send the event to UI
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_no_longer_available',
            'order_id' => $this->order->id,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable))->onQueue('notifications');
    }

    public function toFcm(object $notifiable): array
    {
        return [
            // Using data-only message so it doesn't pop up on the device visually
            'data' => [
                'type' => 'order_no_longer_available',
                'order_id' => (string) $this->order->id,
            ],
        ];
    }
}
