<?php

namespace App\Events;

use App\Models\Courier\Courier;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourierCashLimitExceeded
{
    use Dispatchable, SerializesModels;

    public function __construct(public Courier $courier) {}
}
