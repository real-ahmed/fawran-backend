<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Admin\AdminAuthController;
use App\Enums\AdminPermission;

Route::post('admin/login', [AdminAuthController::class, 'login']);

Route::middleware('auth:api_admin')->prefix('admin')->group(function () {
    Route::post('logout', [AdminAuthController::class, 'logout']);
    Route::post('refresh', [AdminAuthController::class, 'refresh']);
    Route::get('me', [AdminAuthController::class, 'me']);

    Route::get('test', function () {
        return response()->json(['message' => 'Admin API works!']);
    });

    // Delivery Zones
    Route::controller(\App\Http\Controllers\Api\V1\Admin\DeliveryZoneController::class)
        ->prefix('delivery-zones')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:' . AdminPermission::VIEW_DELIVERY_ZONES->value);
            Route::post('/', 'store')->middleware('can:' . AdminPermission::CREATE_DELIVERY_ZONES->value);
            Route::get('/{delivery_zone}', 'show')->middleware('can:' . AdminPermission::VIEW_DELIVERY_ZONES->value);
            Route::put('/{delivery_zone}', 'update')->middleware('can:' . AdminPermission::UPDATE_DELIVERY_ZONES->value);
            Route::delete('/{delivery_zone}', 'destroy')->middleware('can:' . AdminPermission::DELETE_DELIVERY_ZONES->value);
        });

    // Roles & Permissions
    Route::controller(\App\Http\Controllers\Api\V1\Admin\RoleController::class)
        ->prefix('roles')
        ->group(function () {
            Route::get('/permissions', 'permissions')->middleware('can:' . AdminPermission::VIEW_ROLES->value);
            Route::get('/', 'index')->middleware('can:' . AdminPermission::VIEW_ROLES->value);
            Route::post('/', 'store')->middleware('can:' . AdminPermission::CREATE_ROLES->value);
            Route::get('/{role}', 'show')->middleware('can:' . AdminPermission::VIEW_ROLES->value);
            Route::put('/{role}', 'update')->middleware('can:' . AdminPermission::UPDATE_ROLES->value);
            Route::delete('/{role}', 'destroy')->middleware('can:' . AdminPermission::DELETE_ROLES->value);
        });
});
