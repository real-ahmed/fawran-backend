<?php

namespace App\Events;

use App\Models\Order\Delivery;
use App\Models\Order\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderDelivered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
        public Delivery $delivery,
    ) {}
}
