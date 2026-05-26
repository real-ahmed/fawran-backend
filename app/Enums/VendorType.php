<?php

namespace App\Enums;

enum VendorType: string
{
    case RESTAURANT = 'restaurant';
    case GROCERY = 'grocery';
    case PHARMACY = 'pharmacy';

    public function label(): string
    {
        return match ($this) {
            self::RESTAURANT => 'Restaurant',
            self::GROCERY => 'Grocery',
            self::PHARMACY => 'Pharmacy',
        };
    }
}
