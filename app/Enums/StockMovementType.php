<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Sale = 'sale';
    case Purchase = 'purchase';
    case Return = 'return';
    case Adjustment = 'adjustment';
}
