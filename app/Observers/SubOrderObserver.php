<?php

namespace App\Observers;

use App\Events\Vendor\NewSubOrderCreated;
use App\Events\Vendor\SubOrderStatusChanged;
use App\Models\Order\SubOrder;
use BackedEnum;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class SubOrderObserver implements ShouldHandleEventsAfterCommit
{
    public function created(SubOrder $subOrder): void
    {
        NewSubOrderCreated::dispatch($subOrder);
    }

    public function updated(SubOrder $subOrder): void
    {
        if (! $subOrder->wasChanged('status')) {
            return;
        }

        SubOrderStatusChanged::dispatch(
            $subOrder,
            $this->statusValue($subOrder->getOriginal('status')),
            $this->statusValue($subOrder->status)
        );
    }

    private function statusValue(mixed $status): string
    {
        if ($status instanceof BackedEnum) {
            return (string) $status->value;
        }

        return (string) $status;
    }
}
