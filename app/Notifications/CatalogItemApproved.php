<?php

namespace App\Notifications;

use App\Broadcasting\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CatalogItemApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $itemType, // 'Brand' or 'Category'
        public string $itemName
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', FcmChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('messages.catalog_item_approved_title', ['type' => __("messages.{$this->itemType}")]),
            'body' => __('messages.catalog_item_approved_body', [
                'type' => __("messages.{$this->itemType}"),
                'name' => $this->itemName,
            ]),
            'type' => 'catalog_approval',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => __('messages.catalog_item_approved_title', ['type' => __("messages.{$this->itemType}")]),
            'body' => __('messages.catalog_item_approved_body', [
                'type' => __("messages.{$this->itemType}"),
                'name' => $this->itemName,
            ]),
            'type' => 'catalog_approval',
        ]);
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'title' => __('messages.catalog_item_approved_title', ['type' => __("messages.{$this->itemType}")]),
            'body' => __('messages.catalog_item_approved_body', [
                'type' => __("messages.{$this->itemType}"),
                'name' => $this->itemName,
            ]),
            'data' => [
                'type' => 'catalog_approval',
            ],
        ];
    }
}
