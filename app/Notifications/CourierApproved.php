<?php

namespace App\Notifications;

use App\Broadcasting\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CourierApproved extends Notification implements ShouldQueue
{
    use Queueable;

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
        ]);
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
