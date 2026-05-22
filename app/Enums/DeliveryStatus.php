<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case HeadingToStores = 'heading_to_vendors';
    case PickingUp = 'picking_up';
    case HeadingToCustomer = 'heading_to_customer';
    case Completed = 'completed';
}
