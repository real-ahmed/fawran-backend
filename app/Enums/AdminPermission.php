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

    case VIEW_VENDORS = 'view vendors';
    case CREATE_VENDORS = 'create vendors';
    case UPDATE_VENDORS = 'update vendors';
    case DELETE_VENDORS = 'delete vendors';

    case VIEW_ADMINS = 'view admins';
    case CREATE_ADMINS = 'create admins';
    case UPDATE_ADMINS = 'update admins';
    case DELETE_ADMINS = 'delete admins';

    case VIEW_CATEGORIES = 'view categories';
    case CREATE_CATEGORIES = 'create categories';
    case UPDATE_CATEGORIES = 'update categories';
    case DELETE_CATEGORIES = 'delete categories';

    case VIEW_BRANDS = 'view brands';
    case CREATE_BRANDS = 'create brands';
    case UPDATE_BRANDS = 'update brands';
    case DELETE_BRANDS = 'delete brands';

    /**
     * Get all permission values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
