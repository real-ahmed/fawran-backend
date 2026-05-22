<?php

namespace App\Enums;

enum OrderType: string
{
    case Delivery = 'delivery';
    case Pickup = 'pickup';
    case InStore = 'in_store';
}
