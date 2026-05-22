<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cod = 'cod';
    case CreditCard = 'credit_card';
    case Wallet = 'wallet';
    case PosCash = 'pos_cash';
    case PosCard = 'pos_card';
}
