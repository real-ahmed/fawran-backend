<?php

namespace App\Services\Finance\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment\Payment;
use RuntimeException;

/**
 * Placeholder payment gateway used until a real gateway (Paymob, Stripe) is integrated.
 * All methods throw exceptions to signal that online payments are not yet configured.
 */
class NullPaymentGateway implements PaymentGatewayInterface
{
    public function initiatePayment(Payment $payment): array
    {
        throw new RuntimeException('Online payment gateway is not configured. Only COD is currently supported.');
    }

    public function verifyWebhook(array $payload): bool
    {
        throw new RuntimeException('Online payment gateway is not configured.');
    }

    public function getTransactionStatus(string $transactionId): string
    {
        throw new RuntimeException('Online payment gateway is not configured.');
    }
}
