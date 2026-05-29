<?php

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewOrderCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $orderId,
        public readonly string $customerName
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $locale = $notifiable->preferredLocale();

        return [
            'type' => 'new_order_created',
            'order_id' => $this->orderId,
            'title' => __('messages.new_order_title', [], $locale),
            'message' => __('messages.new_order_created', ['name' => $this->customerName, 'id' => $this->orderId], $locale),
            'icon' => 'ShoppingCart',
            'color' => 'primary',
            'action_url' => "/orders/{$this->orderId}",
        ];
    }
}
