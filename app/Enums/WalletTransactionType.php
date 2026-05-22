<?php

namespace App\Enums;

enum WalletTransactionType: string
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case Refund = 'refund';
}
