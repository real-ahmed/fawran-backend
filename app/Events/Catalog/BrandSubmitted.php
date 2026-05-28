<?php

namespace App\Events\Catalog;

use App\Models\Admin;
use App\Models\Catalog\Brand;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BrandSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Brand $brand, public Admin $admin) {}

    /**
     * @return array{brand_id: int, resource: string, message: string}
     */
    public function broadcastWith(): array
    {
        $locale = $this->admin->preferredLocale();
        $name = $this->displayName($locale);

        return [
            'brand_id' => $this->brand->id,
            'resource' => 'brands',
            'message' => __('messages.new_catalog_submission', [
                'type' => __('messages.Brand', [], $locale),
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
        return 'BrandSubmitted';
    }

    private function displayName(string $locale): string
    {
        $name = $this->brand->name;

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
