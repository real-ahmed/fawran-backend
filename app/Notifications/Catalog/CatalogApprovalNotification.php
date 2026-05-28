<?php

namespace App\Notifications\Catalog;

use App\Broadcasting\FcmChannel;
use App\Notifications\Concerns\QueuesNotificationDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

abstract class CatalogApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesNotificationDelivery;

    public function __construct(public string $itemName) {}

    /**
     * @return array<int, class-string|string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', FcmChannel::class];
    }

    /**
     * @return array{title: string, body: string, type: string, item_type: string}
     */
    public function toArray(object $notifiable): array
    {
        return $this->payload();
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload())->onQueue('notifications');
    }

    /**
     * @return array{title: string, body: string, data: array{type: string, item_type: string}}
     */
    public function toFcm(object $notifiable): array
    {
        $payload = $this->payload();

        return [
            'title' => $payload['title'],
            'body' => $payload['body'],
            'data' => [
                'type' => $payload['type'],
                'item_type' => $payload['item_type'],
            ],
        ];
    }

    abstract protected function itemType(): string;

    /**
     * @return array{title: string, body: string, type: string, item_type: string}
     */
    private function payload(): array
    {
        $itemType = $this->itemType();

        return [
            'title' => __('messages.catalog_item_approved_title', ['type' => __("messages.{$itemType}")]),
            'body' => __('messages.catalog_item_approved_body', [
                'type' => __("messages.{$itemType}"),
                'name' => $this->itemName,
            ]),
            'type' => 'catalog_approval',
            'item_type' => $itemType,
        ];
    }
}
