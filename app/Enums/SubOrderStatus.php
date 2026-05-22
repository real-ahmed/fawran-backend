<?php

namespace App\Enums;

enum SubOrderStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Preparing = 'preparing';
    case ReadyForPickup = 'ready_for_pickup';
}
