<?php

namespace App\Enums;

enum VendorPermission: string
{
    case VIEW_DASHBOARD = 'view vendor dashboard';
    case MANAGE_SETTINGS = 'manage vendor settings';

    // Inventory & Products
    case VIEW_PRODUCTS = 'view vendor products';
    case MANAGE_PRODUCTS = 'manage vendor products';
    case MANAGE_INVENTORY = 'manage vendor inventory';

    // Orders
    case VIEW_ORDERS = 'view vendor orders';
    case PROCESS_ORDERS = 'process vendor orders';

    // Roles & Staff
    case VIEW_STAFF = 'view vendor staff';
    case MANAGE_STAFF = 'manage vendor staff';
    case MANAGE_ROLES = 'manage vendor roles';

    // Catalog
    case VIEW_CATEGORIES = 'view vendor categories';
    case PROPOSE_CATEGORIES = 'propose vendor categories';
    case VIEW_BRANDS = 'view vendor brands';
    case PROPOSE_BRANDS = 'propose vendor brands';

    /**
     * Get all permission values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
