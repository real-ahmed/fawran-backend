<?php

namespace App\Enums;

enum AdminPermission: string
{
    case VIEW_DELIVERY_ZONES = 'view delivery zones';
    case CREATE_DELIVERY_ZONES = 'create delivery zones';
    case UPDATE_DELIVERY_ZONES = 'update delivery zones';
    case DELETE_DELIVERY_ZONES = 'delete delivery zones';

    case VIEW_ROLES = 'view roles';
    case CREATE_ROLES = 'create roles';
    case UPDATE_ROLES = 'update roles';
    case DELETE_ROLES = 'delete roles';

    /**
     * Get all permission values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
