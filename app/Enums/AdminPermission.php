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
    case APPROVE_CATEGORIES = 'approve categories';

    case VIEW_BRANDS = 'view brands';
    case CREATE_BRANDS = 'create brands';
    case UPDATE_BRANDS = 'update brands';
    case DELETE_BRANDS = 'delete brands';
    case APPROVE_BRANDS = 'approve brands';

    case VIEW_CUSTOMERS = 'view customers';
    case UPDATE_CUSTOMERS = 'update customers';

    case VIEW_COURIERS = 'view couriers';
    case UPDATE_COURIERS = 'update couriers';
    case DELETE_COURIERS = 'delete couriers';
    case APPROVE_COURIERS = 'approve couriers';

    case VIEW_MASTER_PRODUCTS = 'view master products';
    case CREATE_MASTER_PRODUCTS = 'create master products';
    case UPDATE_MASTER_PRODUCTS = 'update master products';
    case DELETE_MASTER_PRODUCTS = 'delete master products';
    case APPROVE_MASTER_PRODUCTS = 'approve master products';

    case VIEW_ORDERS = 'view orders';
    case CANCEL_ORDERS = 'cancel orders';

    case VIEW_HOT_ZONES = 'view hot zones';
    case CREATE_HOT_ZONES = 'create hot zones';
    case UPDATE_HOT_ZONES = 'update hot zones';
    case DELETE_HOT_ZONES = 'delete hot zones';

    case VIEW_FINANCES = 'view finances';
    case MANAGE_SETTLEMENTS = 'manage settlements';
    case MANAGE_PAYOUTS = 'manage payouts';
    case MANAGE_REFUNDS = 'manage refunds';
    case MANAGE_SYSTEM_SETTINGS = 'manage system settings';

    /**
     * Get all permission values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
