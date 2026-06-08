<?php

namespace App\Enums;

enum VendorPermission: string
{
    case VIEW_DASHBOARD = 'view vendor dashboard';
    case MANAGE_SETTINGS = 'manage vendor settings';

    // Profile
    case MANAGE_PROFILE = 'manage vendor profile';

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

    // Delivery Zones
    case VIEW_DELIVERY_ZONES = 'view vendor delivery zones';
    case MANAGE_DELIVERY_ZONES = 'manage vendor delivery zones';

    // Finances
    case VIEW_FINANCES = 'view vendor finances';
    case MANAGE_FINANCES = 'manage vendor finances';

    // Inventory (type-specific)
    case VIEW_INVENTORY = 'view vendor inventory';
    case MANAGE_INVENTORY_ADJUSTMENTS = 'manage vendor inventory adjustments';

    // Suppliers & Purchase Orders
    case VIEW_SUPPLIERS = 'view vendor suppliers';
    case MANAGE_SUPPLIERS = 'manage vendor suppliers';
    case VIEW_PURCHASE_ORDERS = 'view vendor purchase orders';
    case MANAGE_PURCHASE_ORDERS = 'manage vendor purchase orders';

    // Subscription
    case VIEW_SUBSCRIPTION = 'view vendor subscription';

    /**
     * Get all permission values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
