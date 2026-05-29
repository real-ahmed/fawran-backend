<?php

namespace App\Listeners\Finance;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\WalletTransactionType;
use App\Events\OrderDelivered;
use App\Services\Finance\PlatformWalletService;
use App\Services\Finance\WalletService;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreditCourierDeliveryFee implements ShouldQueue
{
    public string $queue = 'default';

    public function __construct(
        private WalletService $walletService,
        private PlatformWalletService $platformWalletService,
    ) {}

    public function handle(OrderDelivered $event): void
    {
        $order = $event->order;
        $delivery = $event->delivery;

        $order->loadMissing('payments');

        // Only process online-paid orders (non-COD)
        $hasOnlinePayment = $order->payments
            ->contains(fn ($p) => $p->payment_method !== PaymentMethod::Cod
                && $p->status === PaymentStatus::Successful
            );

        if (! $hasOnlinePayment) {
            return;
        }

        $courierFee = (float) $delivery->fee_share;
        if ($courierFee <= 0) {
            return;
        }

        $courier = $delivery->courier;
        if (! $courier?->user) {
            return;
        }

        // Credit courier wallet with delivery fee
        $this->walletService->deposit(
            $courier->user,
            $courierFee,
            WalletTransactionType::DeliveryEarning,
            $delivery,
        );

        // Credit platform wallet with the net amount (total delivery fee minus courier cut)
        $order->loadMissing('orderDelivery');
        $totalDeliveryFee = (float) ($order->orderDelivery?->total_delivery_fee ?? 0);
        $platformShare = $totalDeliveryFee - $courierFee;

        if ($platformShare > 0) {
            $this->platformWalletService->credit($platformShare);
        }
    }
}
