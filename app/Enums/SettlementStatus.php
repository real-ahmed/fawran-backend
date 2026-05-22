<?php

namespace App\Enums;

enum SettlementStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Disputed = 'disputed';
}
