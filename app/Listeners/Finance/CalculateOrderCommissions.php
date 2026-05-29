<?php

namespace App\Listeners\Finance;

use App\Events\OrderConfirmed;
use App\Services\Finance\CommissionCalculator;
use Illuminate\Contracts\Queue\ShouldQueue;

class CalculateOrderCommissions implements ShouldQueue
{
    public string $queue = 'default';

    public function __construct(private CommissionCalculator $calculator) {}

    public function handle(OrderConfirmed $event): void
    {
        $this->calculator->calculateForOrder($event->order);
    }
}
