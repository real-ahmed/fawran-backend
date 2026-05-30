<?php

namespace App\Events\Courier;

use App\Models\Admin;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourierLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $courierId,
        public int $orderId,
        public float $latitude,
        public float $longitude,
        public Admin $admin,
        public ?int $visitedVendorId = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.'.$this->admin->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'CourierLocationUpdated';
    }

    public function broadcastWith(): array
    {
        $data = [
            'courier_id' => $this->courierId,
            'order_id' => $this->orderId,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];

        if ($this->visitedVendorId !== null) {
            $data['visited_vendor_id'] = $this->visitedVendorId;
        }

        return $data;
    }
}
