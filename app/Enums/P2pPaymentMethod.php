<?php

namespace App\Enums;

enum P2pPaymentMethod: string
{
    case CreditCard = 'credit_card';
    case Wallet = 'wallet';
    case Cod = 'cod';
}
