<?php

namespace App\Events\Catalog;

use App\Models\Admin;
use App\Models\Catalog\Category;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CategorySubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Category $category, public Admin $admin) {}

    /**
     * @return array{category_id: int, resource: string, message: string}
     */
    public function broadcastWith(): array
    {
        $locale = $this->admin->preferredLocale();
        $name = $this->displayName($locale);

        return [
            'category_id' => $this->category->id,
            'resource' => 'categories',
            'message' => __('messages.new_catalog_submission', [
                'type' => __('messages.Category', [], $locale),
                'name' => $name,
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

    public function broadcastAs(): string
    {
        return 'CategorySubmitted';
    }

    private function displayName(string $locale): string
    {
        $name = $this->category->name;

        if (is_string($name)) {
            return $name;
        }

        return $name[$locale]
            ?? $name['en']
            ?? $name['ar']
            ?? current((array) $name)
            ?: __('messages.unknown', [], $locale);
    }
}
