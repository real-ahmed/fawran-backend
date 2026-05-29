<?php

namespace App\Contracts;

use App\Models\Payment\Payment;

interface PaymentGatewayInterface
{
    /**
     * Initiate a payment session with the gateway.
     *
     * @return array{payment_url: string, transaction_id: string}
     */
    public function initiatePayment(Payment $payment): array;

    /**
     * Verify a webhook callback from the payment gateway.
     *
     * @param  array<string, mixed>  $payload
     */
    public function verifyWebhook(array $payload): bool;

    /**
     * Query the gateway for a transaction's current status.
     */
    public function getTransactionStatus(string $transactionId): string;
}
