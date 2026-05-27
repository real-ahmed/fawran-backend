<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Models\Courier\Courier;
use App\Models\Admin;

class CourierApplicationSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Courier $courier, public Admin $admin)
    {
    }

    public function broadcastWith(): array
    {
        $locale = $this->admin->preferredLocale();
        $name = $this->courier->user->name ?? __('messages.unknown', [], $locale);
        
        return [
            'courier_id' => $this->courier->id,
            'message' => __('messages.new_courier_application', ['name' => $name], $locale),
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.' . $this->admin->id)
        ];
    }
}
