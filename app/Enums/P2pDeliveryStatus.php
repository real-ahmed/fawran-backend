<?php

namespace App\Enums;

enum P2pDeliveryStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case PickedUp = 'picked_up';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
}
