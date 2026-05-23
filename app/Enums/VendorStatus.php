<?php

namespace App\Enums;

enum VendorStatus: string
{
    case ONLINE = 'online';
    case BUSY = 'busy';
    case OFFLINE = 'offline';

    /**
     * Get all status values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
