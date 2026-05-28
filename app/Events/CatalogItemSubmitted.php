<?php

namespace App\Events;

use App\Models\Admin;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CatalogItemSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $itemType,
        public int $itemId,
        public array|string $itemName,
        public Admin $admin,
    ) {}

    public function broadcastWith(): array
    {
        $locale = $this->admin->preferredLocale();

        return [
            'item_type' => $this->itemType,
            'item_id' => $this->itemId,
            'message' => __('messages.new_catalog_submission', [
                'type' => __("messages.{$this->itemType}", [], $locale),
                'name' => $this->displayName($locale),
            ], $locale),
        ];
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.'.$this->admin->id),
        ];
    }

    private function displayName(string $locale): string
    {
        if (is_string($this->itemName)) {
            return $this->itemName;
        }

        $name = $this->itemName[$locale]
            ?? $this->itemName['en']
            ?? $this->itemName['ar']
            ?? current($this->itemName);

        return $name ?: __('messages.unknown', [], $locale);
    }
}
