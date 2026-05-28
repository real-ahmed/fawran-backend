<?php

namespace App\Notifications;

use App\Broadcasting\FcmChannel;
use App\Notifications\Concerns\QueuesNotificationDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CourierApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesNotificationDelivery;

    public function __construct(public string $courierName) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', FcmChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('messages.courier_approved_title'),
            'body' => __('messages.courier_approved_body', ['name' => $this->courierName]),
            'type' => 'courier_approval',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => __('messages.courier_approved_title'),
            'body' => __('messages.courier_approved_body', ['name' => $this->courierName]),
            'type' => 'courier_approval',
        ])->onQueue('notifications');
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'title' => __('messages.courier_approved_title'),
            'body' => __('messages.courier_approved_body', ['name' => $this->courierName]),
            'data' => [
                'type' => 'courier_approval',
            ],
        ];
    }
}
