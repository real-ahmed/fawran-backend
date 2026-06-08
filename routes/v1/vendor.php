<?php

use App\Enums\VendorPermission;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\UserAuthController;
use App\Http\Controllers\Api\V1\Vendor\CatalogController;
use App\Http\Controllers\Api\V1\Vendor\DeliveryZoneController;
use App\Http\Controllers\Api\V1\Vendor\InventoryController;
use App\Http\Controllers\Api\V1\Vendor\OrderController;
use App\Http\Controllers\Api\V1\Vendor\ProductOptionController;
use App\Http\Controllers\Api\V1\Vendor\PurchaseOrderController;
use App\Http\Controllers\Api\V1\Vendor\SupplierController;
use App\Http\Controllers\Api\V1\Vendor\VendorDashboardController;
use App\Http\Controllers\Api\V1\Vendor\VendorFinanceController;
use App\Http\Controllers\Api\V1\Vendor\VendorItemController;
use App\Http\Controllers\Api\V1\Vendor\VendorProfileController;
use App\Http\Controllers\Api\V1\Vendor\VendorRoleController;
use App\Http\Controllers\Api\V1\Vendor\VendorStaffController;
use App\Http\Controllers\Api\V1\Vendor\VendorSubscriptionController;
use App\Http\Middleware\EnsureVendorHasInventory;
use Illuminate\Support\Facades\Route;

Route::post('vendor/login', [UserAuthController::class, 'login'])->middleware('throttle:auth');
Route::post('vendor/forgot-password', [UserAuthController::class, 'forgotPassword'])->middleware('throttle:auth');
Route::post('vendor/verify-reset-otp', [UserAuthController::class, 'verifyResetOtp'])->middleware('throttle:auth');
Route::post('vendor/reset-password', [UserAuthController::class, 'resetPassword'])->middleware('throttle:auth');
Route::post('vendor/refresh', [UserAuthController::class, 'refresh']);

Route::middleware(['auth:api', 'vendor.team'])->prefix('vendor')->group(function () {
    Route::post('logout', [UserAuthController::class, 'logout']);
    Route::get('me', [UserAuthController::class, 'me']);
    Route::put('profile/settings', [SettingsController::class, 'update']);

    // Profile
    Route::controller(VendorProfileController::class)
        ->prefix('profile')
        ->group(function () {
            Route::get('/', 'show');
            Route::put('/', 'update')->middleware('can:'.VendorPermission::MANAGE_PROFILE->value);
            Route::put('/status', 'updateStatus')->middleware('can:'.VendorPermission::MANAGE_PROFILE->value);
            Route::put('/working-hours', 'updateWorkingHours')->middleware('can:'.VendorPermission::MANAGE_PROFILE->value);
        });

    // Dashboard
    Route::controller(VendorDashboardController::class)
        ->prefix('dashboard')
        ->group(function () {
            Route::get('/metrics', 'metrics')->middleware('can:'.VendorPermission::VIEW_DASHBOARD->value);
        });

    // Items
    Route::controller(VendorItemController::class)
        ->prefix('items')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.VendorPermission::MANAGE_PRODUCTS->value);
            Route::get('/{item}', 'show')->middleware('can:'.VendorPermission::MANAGE_PRODUCTS->value);
            Route::post('/', 'store')->middleware('can:'.VendorPermission::MANAGE_PRODUCTS->value);
            Route::put('/{item}', 'update')->middleware('can:'.VendorPermission::MANAGE_PRODUCTS->value);
            Route::delete('/{item}', 'destroy')->middleware('can:'.VendorPermission::MANAGE_PRODUCTS->value);
            Route::put('/{item}/status', 'updateStatus')->middleware('can:'.VendorPermission::MANAGE_PRODUCTS->value);
        });

    Route::controller(ProductOptionController::class)
        ->prefix('items/{item}/options')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.VendorPermission::MANAGE_PRODUCTS->value);
            Route::post('/', 'store')->middleware('can:'.VendorPermission::MANAGE_PRODUCTS->value);
            Route::get('/{option}', 'show')->middleware('can:'.VendorPermission::MANAGE_PRODUCTS->value);
            Route::put('/{option}', 'update')->middleware('can:'.VendorPermission::MANAGE_PRODUCTS->value);
            Route::delete('/{option}', 'destroy')->middleware('can:'.VendorPermission::MANAGE_PRODUCTS->value);
        });

    Route::controller(CatalogController::class)
        ->prefix('catalog')
        ->group(function () {
            Route::get('/categories', 'categories')->middleware('can:'.VendorPermission::MANAGE_PRODUCTS->value);
            Route::get('/brands', 'brands')->middleware('can:'.VendorPermission::MANAGE_PRODUCTS->value);
            Route::get('/master-products', 'masterProducts')->middleware('can:'.VendorPermission::MANAGE_PRODUCTS->value);
            Route::post('/master-products', 'storeMasterProduct')->middleware('can:'.VendorPermission::MANAGE_PRODUCTS->value);
        });

    // Orders
    Route::controller(OrderController::class)
        ->prefix('orders')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.VendorPermission::VIEW_ORDERS->value);
            Route::get('/status-counts', 'statusCounts')->middleware('can:'.VendorPermission::VIEW_ORDERS->value);
            Route::get('/{subOrder}', 'show')->middleware('can:'.VendorPermission::VIEW_ORDERS->value);
            Route::put('/{subOrder}/status', 'updateStatus')->middleware('can:'.VendorPermission::PROCESS_ORDERS->value);
        });

    // Delivery Zones
    Route::controller(DeliveryZoneController::class)
        ->prefix('delivery-zones')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.VendorPermission::VIEW_DELIVERY_ZONES->value);
            Route::post('/', 'store')->middleware('can:'.VendorPermission::MANAGE_DELIVERY_ZONES->value);
            Route::put('/{zone}', 'update')->middleware('can:'.VendorPermission::MANAGE_DELIVERY_ZONES->value);
            Route::delete('/{zone}', 'destroy')->middleware('can:'.VendorPermission::MANAGE_DELIVERY_ZONES->value);
        });

    // Inventory (Type-Guarded)
    Route::controller(InventoryController::class)
        ->prefix('inventory')
        ->middleware(EnsureVendorHasInventory::class)
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.VendorPermission::VIEW_INVENTORY->value);
            Route::put('/{inventory}', 'update')->middleware('can:'.VendorPermission::MANAGE_INVENTORY->value);
            Route::post('/adjustments', 'adjust')->middleware('can:'.VendorPermission::MANAGE_INVENTORY_ADJUSTMENTS->value);
        });

    // Suppliers (Type-Guarded, uses same middleware as Inventory typically, or its own)
    Route::controller(SupplierController::class)
        ->prefix('suppliers')
        ->middleware(EnsureVendorHasInventory::class)
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.VendorPermission::VIEW_SUPPLIERS->value);
            Route::post('/', 'store')->middleware('can:'.VendorPermission::MANAGE_SUPPLIERS->value);
            Route::put('/{supplier}', 'update')->middleware('can:'.VendorPermission::MANAGE_SUPPLIERS->value);
            Route::delete('/{supplier}', 'destroy')->middleware('can:'.VendorPermission::MANAGE_SUPPLIERS->value);
        });

    // Purchase Orders (Type-Guarded)
    Route::controller(PurchaseOrderController::class)
        ->prefix('purchase-orders')
        ->middleware(EnsureVendorHasInventory::class)
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.VendorPermission::VIEW_PURCHASE_ORDERS->value);
            Route::get('/{purchaseOrder}', 'show')->middleware('can:'.VendorPermission::VIEW_PURCHASE_ORDERS->value);
            Route::post('/', 'store')->middleware('can:'.VendorPermission::MANAGE_PURCHASE_ORDERS->value);
            Route::put('/{purchaseOrder}/status', 'updateStatus')->middleware('can:'.VendorPermission::MANAGE_PURCHASE_ORDERS->value);
        });

    // Roles & Permissions
    Route::controller(VendorRoleController::class)
        ->prefix('roles')
        ->group(function () {
            Route::get('/permissions', 'permissions')->middleware('can:'.VendorPermission::MANAGE_ROLES->value);
            Route::get('/', 'index')->middleware('can:'.VendorPermission::MANAGE_ROLES->value);
            Route::post('/', 'store')->middleware('can:'.VendorPermission::MANAGE_ROLES->value);
            Route::get('/{role}', 'show')->middleware('can:'.VendorPermission::MANAGE_ROLES->value);
            Route::put('/{role}', 'update')->middleware('can:'.VendorPermission::MANAGE_ROLES->value);
            Route::delete('/{role}', 'destroy')->middleware('can:'.VendorPermission::MANAGE_ROLES->value);
        });

    // Staff Management
    Route::controller(VendorStaffController::class)
        ->prefix('staff')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.VendorPermission::MANAGE_STAFF->value);
            Route::post('/', 'store')->middleware('can:'.VendorPermission::MANAGE_STAFF->value);
            Route::get('/{staff}', 'show')->middleware('can:'.VendorPermission::MANAGE_STAFF->value);
            Route::put('/{staff}', 'update')->middleware('can:'.VendorPermission::MANAGE_STAFF->value);
            Route::delete('/{staff}', 'destroy')->middleware('can:'.VendorPermission::MANAGE_STAFF->value);
        });

    // Finances
    Route::controller(VendorFinanceController::class)
        ->prefix('finances')
        ->group(function () {
            Route::get('/wallet', 'wallet')->middleware('can:'.VendorPermission::VIEW_FINANCES->value);
            Route::get('/payout-requests', 'payoutRequests')->middleware('can:'.VendorPermission::VIEW_FINANCES->value);
            Route::post('/payout-requests', 'storePayoutRequest')->middleware('can:'.VendorPermission::MANAGE_FINANCES->value);
        });

    // Subscriptions
    Route::controller(VendorSubscriptionController::class)
        ->prefix('subscriptions')
        ->group(function () {
            Route::get('/current', 'current')->middleware('can:'.VendorPermission::VIEW_SUBSCRIPTION->value);
            Route::get('/plans', 'plans')->middleware('can:'.VendorPermission::VIEW_SUBSCRIPTION->value);
        });
});
