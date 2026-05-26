<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Api\V1\Admin\AdminAuthController;
use App\Http\Controllers\Api\V1\Admin\AdminUserController;
use App\Http\Controllers\Api\V1\Admin\BrandController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\DeliveryZoneController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Admin\VendorController;
use App\Http\Controllers\Api\V1\SettingsController;
use Illuminate\Support\Facades\Route;

Route::post('admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:auth');

Route::middleware('auth:api_admin')->prefix('admin')->group(function () {
    Route::post('logout', [AdminAuthController::class, 'logout']);
    Route::post('refresh', [AdminAuthController::class, 'refresh']);
    Route::get('me', [AdminAuthController::class, 'me']);
    Route::put('profile/settings', [SettingsController::class, 'update']);

    // Delivery Zones
    Route::controller(DeliveryZoneController::class)
        ->prefix('delivery-zones')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::VIEW_DELIVERY_ZONES->value);
            Route::post('/', 'store')->middleware('can:'.AdminPermission::CREATE_DELIVERY_ZONES->value);
            Route::get('/{delivery_zone}', 'show')->middleware('can:'.AdminPermission::VIEW_DELIVERY_ZONES->value);
            Route::put('/{delivery_zone}', 'update')->middleware('can:'.AdminPermission::UPDATE_DELIVERY_ZONES->value);
            Route::delete('/{delivery_zone}', 'destroy')->middleware('can:'.AdminPermission::DELETE_DELIVERY_ZONES->value);
        });

    // Roles & Permissions
    Route::controller(RoleController::class)
        ->prefix('roles')
        ->group(function () {
            Route::get('/permissions', 'permissions')->middleware('can:'.AdminPermission::VIEW_ROLES->value);
            Route::get('/', 'index')->middleware('can:'.AdminPermission::VIEW_ROLES->value);
            Route::post('/', 'store')->middleware('can:'.AdminPermission::CREATE_ROLES->value);
            Route::get('/{role}', 'show')->middleware('can:'.AdminPermission::VIEW_ROLES->value);
            Route::put('/{role}', 'update')->middleware('can:'.AdminPermission::UPDATE_ROLES->value);
            Route::delete('/{role}', 'destroy')->middleware('can:'.AdminPermission::DELETE_ROLES->value);
        });

    // Vendors Management
    Route::controller(VendorController::class)
        ->prefix('vendors')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::VIEW_VENDORS->value);
            Route::post('/', 'store')->middleware('can:'.AdminPermission::CREATE_VENDORS->value);
            Route::get('/{vendor}', 'show')->middleware('can:'.AdminPermission::VIEW_VENDORS->value);
            Route::put('/{vendor}', 'update')->middleware('can:'.AdminPermission::UPDATE_VENDORS->value);
            Route::delete('/{vendor}', 'destroy')->middleware('can:'.AdminPermission::DELETE_VENDORS->value);
        });
    // Categories Management
    Route::controller(CategoryController::class)
        ->prefix('categories')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::VIEW_CATEGORIES->value);
            Route::post('/', 'store')->middleware('can:'.AdminPermission::CREATE_CATEGORIES->value);
            Route::get('/{category}', 'show')->middleware('can:'.AdminPermission::VIEW_CATEGORIES->value);
            Route::put('/{category}', 'update')->middleware('can:'.AdminPermission::UPDATE_CATEGORIES->value);
            Route::delete('/{category}', 'destroy')->middleware('can:'.AdminPermission::DELETE_CATEGORIES->value);
        });

    // Brands Management
    Route::controller(BrandController::class)
        ->prefix('brands')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::VIEW_BRANDS->value);
            Route::post('/', 'store')->middleware('can:'.AdminPermission::CREATE_BRANDS->value);
            Route::get('/{brand}', 'show')->middleware('can:'.AdminPermission::VIEW_BRANDS->value);
            Route::put('/{brand}', 'update')->middleware('can:'.AdminPermission::UPDATE_BRANDS->value);
            Route::delete('/{brand}', 'destroy')->middleware('can:'.AdminPermission::DELETE_BRANDS->value);
        });

    // Admins Management
    Route::controller(AdminUserController::class)
        ->prefix('admins')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::VIEW_ADMINS->value);
            Route::post('/', 'store')->middleware('can:'.AdminPermission::CREATE_ADMINS->value);
            Route::get('/{adminUser}', 'show')->middleware('can:'.AdminPermission::VIEW_ADMINS->value);
            Route::put('/{adminUser}', 'update')->middleware('can:'.AdminPermission::UPDATE_ADMINS->value);
            Route::delete('/{adminUser}', 'destroy')->middleware('can:'.AdminPermission::DELETE_ADMINS->value);
        });
});
