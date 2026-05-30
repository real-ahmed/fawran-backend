<?php

namespace App\Notifications\Courier;

use App\Broadcasting\FcmChannel;
use App\Models\Order\Order;
use App\Notifications\Concerns\QueuesNotificationDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewDeliveryRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesNotificationDelivery;

    public function __construct(
        public Order $order,
        public float $distanceKm,
        public float $feeShare
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', FcmChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('messages.new_delivery_request_title'),
            'body' => __('messages.new_delivery_request_body', ['fee' => $this->feeShare]),
            'type' => 'new_delivery_request',
            'order_id' => $this->order->id,
            'distance_km' => $this->distanceKm,
            'fee_share' => $this->feeShare,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable))->onQueue('notifications');
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'title' => __('messages.new_delivery_request_title'),
            'body' => __('messages.new_delivery_request_body', ['fee' => $this->feeShare]),
            'data' => [
                'type' => 'new_delivery_request',
                'order_id' => (string) $this->order->id,
                'distance_km' => (string) $this->distanceKm,
                'fee_share' => (string) $this->feeShare,
            ],
        ];
    }
}
