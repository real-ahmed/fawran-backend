<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case Pending = 'pending';
    case Received = 'received';
}
