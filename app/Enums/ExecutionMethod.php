<?php

namespace App\Enums;

enum ExecutionMethod: string
{
    case Cash = 'cash';
    case Wallet = 'wallet';
    case Bank = 'bank';
}
