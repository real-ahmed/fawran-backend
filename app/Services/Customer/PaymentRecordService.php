<?php

namespace App\Services\Customer;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\WalletTransactionType;
use App\Models\Order\Order;
use App\Models\Payment\Payment;
use App\Models\Payment\Wallet;
use App\Models\Payment\WalletTransaction;
use Exception;

class PaymentRecordService
{
    /**
     * Create a payment record for the order based on the payment method.
     *
     * - COD: Payment is recorded as pending (settled on delivery)
     * - CreditCard: Payment is recorded as pending (gateway processes separately)
     * - Wallet: Balance is deducted immediately, payment recorded as successful
     *
     * @throws Exception
     */
    public function recordPayment(Order $order, PaymentMethod $method, float $amount): Payment
    {
        return match ($method) {
            PaymentMethod::Wallet => $this->processWalletPayment($order, $amount),
            default => $this->createPendingPayment($order, $method, $amount),
        };
    }

    private function createPendingPayment(Order $order, PaymentMethod $method, float $amount): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'amount' => $amount,
            'payment_method' => $method->value,
            'status' => PaymentStatus::Pending->value,
        ]);
    }

    /**
     * @throws Exception
     */
    private function processWalletPayment(Order $order, float $amount): Payment
    {
        $order->loadMissing('customer.customer');
        $customerId = $order->customer?->customer_id;

        if (! $customerId) {
            throw new Exception(__('messages.customer_not_found'));
        }

        $wallet = Wallet::where('user_id', $customerId)->lockForUpdate()->first();

        if (! $wallet || (float) $wallet->balance < $amount) {
            throw new Exception(__('messages.insufficient_wallet_balance'));
        }

        $wallet->decrement('balance', $amount);

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'amount' => -$amount,
            'type' => WalletTransactionType::Withdrawal->value,
            'reference_type' => Order::class,
            'reference_id' => $order->id,
        ]);

        return Payment::create([
            'order_id' => $order->id,
            'amount' => $amount,
            'payment_method' => PaymentMethod::Wallet->value,
            'status' => PaymentStatus::Successful->value,
        ]);
    }
}
