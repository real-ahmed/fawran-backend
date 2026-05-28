<?php

namespace App\Notifications\Admin;

use App\Broadcasting\FcmChannel;
use App\Notifications\Concerns\QueuesNotificationDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CatalogSubmissionReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesNotificationDelivery;

    public function __construct(public string $itemName, public string $itemType) {}

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
            'title' => __('messages.catalog_submission_received'),
            'body' => __('messages.catalog_submission_received_body', ['name' => $this->itemName, 'type' => $this->itemType] ?? "A new {$this->itemType} has been submitted to the catalog and is pending approval."),
            'type' => 'catalog_submission',
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
