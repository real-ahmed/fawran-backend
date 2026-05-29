<?php

namespace App\Enums;

enum WalletTransactionType: string
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case Refund = 'refund';
    case DeliveryEarning = 'delivery_earning';
    case CommissionEarning = 'commission_earning';
    case CodDeduction = 'cod_deduction';
    case PayoutWithdrawal = 'payout_withdrawal';
}
