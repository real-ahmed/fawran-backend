<?php

namespace App\Enums;

enum RefundResolution: string
{
    case WalletCredit = 'wallet_credit';
    case GatewayRefund = 'gateway_refund';
}
