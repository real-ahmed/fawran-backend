<?php

namespace App\Listeners\Finance;

use App\Events\CourierCashLimitExceeded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class HandleCourierCashLimit implements ShouldQueue
{
    public string $queue = 'default';

    /**
     * Soft-block: flag the courier so COD orders are filtered away from them.
     * We do NOT set is_online = false (hard block). Instead, we set a flag
     * that the order assignment system uses to skip COD orders for this courier.
     */
    public function handle(CourierCashLimitExceeded $event): void
    {
        $courier = $event->courier;

        // Set the cod_blocked flag (using the existing is_online for now — soft filter at query level)
        // The courier can still receive non-COD orders
        $courier->update(['cod_blocked' => true]);

        Log::warning('Courier cash limit exceeded', [
            'courier_id' => $courier->id,
            'user_id' => $courier->user_id,
        ]);
    }
}
