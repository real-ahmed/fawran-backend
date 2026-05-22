<?php

namespace App\Enums;

enum VendorPermission: string
{
    case VIEW_DASHBOARD = 'view store dashboard';
    case MANAGE_SETTINGS = 'manage store settings';
    
    // Inventory & Products
    case VIEW_PRODUCTS = 'view store products';
    case MANAGE_PRODUCTS = 'manage store products';
    case MANAGE_INVENTORY = 'manage store inventory';
    
    // Orders
    case VIEW_ORDERS = 'view store orders';
    case PROCESS_ORDERS = 'process store orders';
    
    // Roles & Staff
    case VIEW_STAFF = 'view store staff';
    case MANAGE_STAFF = 'manage store staff';
    case MANAGE_ROLES = 'manage store roles';

    /**
     * Get all permission values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
