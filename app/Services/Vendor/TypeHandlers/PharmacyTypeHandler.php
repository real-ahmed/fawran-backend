<?php

namespace App\Services\Vendor\TypeHandlers;

/**
 * Pharmacy shares the same inventory-based behavior as Grocery.
 * Extend GroceryTypeHandler to avoid duplication; override if pharmacies
 * ever need distinct logic (e.g., prescription tracking, expiry dates).
 */
class PharmacyTypeHandler extends GroceryTypeHandler
{
    //
}
