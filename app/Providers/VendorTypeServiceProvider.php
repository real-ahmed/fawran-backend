<?php

namespace App\Providers;

use App\Enums\VendorType;
use App\Services\Vendor\TypeHandlers\GroceryTypeHandler;
use App\Services\Vendor\TypeHandlers\PharmacyTypeHandler;
use App\Services\Vendor\TypeHandlers\RestaurantTypeHandler;
use App\Services\Vendor\VendorTypeRegistry;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class VendorTypeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(VendorTypeRegistry::class, function (Application $app): VendorTypeRegistry {
            $registry = new VendorTypeRegistry;

            $registry->register(VendorType::RESTAURANT, $app->make(RestaurantTypeHandler::class));
            $registry->register(VendorType::GROCERY, $app->make(GroceryTypeHandler::class));
            $registry->register(VendorType::PHARMACY, $app->make(PharmacyTypeHandler::class));

            return $registry;
        });
    }
}
