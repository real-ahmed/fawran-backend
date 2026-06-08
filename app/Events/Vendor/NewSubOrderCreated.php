<?php

namespace App\Events\Vendor;

use App\Models\Order\SubOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewSubOrderCreated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SubOrder $subOrder
    ) {}

    public function broadcastWith(): array
    {
        return [
            'sub_order_id' => $this->subOrder->id,
            'message' => __('messages.new_sub_order_assigned', [
                'id' => $this->subOrder->id,
            ]),
            'amount' => (float) $this->subOrder->sub_total,
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('vendor.'.$this->subOrder->vendor_id),
        ];
    }
}
