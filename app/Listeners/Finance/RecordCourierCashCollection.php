<?php

namespace App\Listeners\Finance;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\CourierCashLimitExceeded;
use App\Events\OrderDelivered;
use App\Services\Finance\CashCollectionService;
use App\Services\Finance\PlatformWalletService;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecordCourierCashCollection implements ShouldQueue
{
    public string $queue = 'default';

    public function __construct(
        private CashCollectionService $cashService,
        private PlatformWalletService $platformWalletService,
    ) {}

    public function handle(OrderDelivered $event): void
    {
        $order = $event->order;
        $delivery = $event->delivery;

        $order->loadMissing(['payments', 'orderDelivery']);

        // Only process COD payments
        $codPayment = $order->payments
            ->firstWhere('payment_method', PaymentMethod::Cod);

        if (! $codPayment) {
            return;
        }

        // Record the cash collection
        $collection = $this->cashService->recordCollection($order, $delivery);

        // Mark payment as successful (cash received)
        $codPayment->update(['status' => PaymentStatus::Successful]);

        // Credit platform wallet in real-time
        $this->platformWalletService->credit((float) $collection->amount_owed_to_platform);

        // Check if courier exceeded cash limit
        $courier = $delivery->courier;
        if ($courier && $this->cashService->isOverCashLimit($courier)) {
            CourierCashLimitExceeded::dispatch($courier);
        }
    }
}
