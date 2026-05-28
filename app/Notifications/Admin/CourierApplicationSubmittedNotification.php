<?php

namespace App\Notifications\Admin;

use App\Broadcasting\FcmChannel;
use App\Notifications\Concerns\QueuesNotificationDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CourierApplicationSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesNotificationDelivery;

    public function __construct(public string $courierName) {}

    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];

        // if (config('firebase.projects.app.credentials')) {
        //     $channels[] = FcmChannel::class;
        // }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('messages.courier_application_submitted'),
            'body' => __('messages.courier_application_submitted_body', ['name' => $this->courierName] ?? 'A new courier application has been submitted and is pending approval.'),
            'type' => 'courier_application',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable))->onQueue('notifications');
    }

    public function toFcm(object $notifiable): array
    {
        $data = $this->toArray($notifiable);

        return [
            'title' => $data['title'],
            'body' => $data['body'],
            'data' => [
                'type' => $data['type'],
            ],
        ];
    }
}
