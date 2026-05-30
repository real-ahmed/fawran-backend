<?php

namespace App\Enums;

enum SubOrderStatus: string
{
    case Pending = 'pending';
    case Preparing = 'preparing';
    case ReadyForPickup = 'ready_for_pickup';
    case PickedUp = 'picked_up';
}
