<?php

namespace App\Events\Vendor;

use App\Models\Order\SubOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SubOrderStatusChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SubOrder $subOrder,
        public string $fromStatus,
        public string $toStatus
    ) {}

    public function broadcastWith(): array
    {
        return [
            'sub_order_id' => $this->subOrder->id,
            'from_status' => $this->fromStatus,
            'to_status' => $this->toStatus,
            'message' => __('messages.sub_order_status_updated', [
                'id' => $this->subOrder->id,
                'status' => Str::headline($this->toStatus),
            ]),
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('vendor.'.$this->subOrder->vendor_id),
        ];
    }
}
