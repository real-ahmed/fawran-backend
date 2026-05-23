<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserAuthController;

Route::post('vendor/login', [UserAuthController::class, 'login'])->middleware('throttle:auth');

Route::middleware(['auth:api', 'vendor.team'])->prefix('vendor')->group(function () {
    Route::post('logout', [UserAuthController::class, 'logout']);
    Route::post('refresh', [UserAuthController::class, 'refresh']);
    Route::get('me', [UserAuthController::class, 'me']);
    Route::put('profile/settings', [\App\Http\Controllers\Api\V1\SettingsController::class, 'update']);



    // Roles & Permissions
    Route::controller(\App\Http\Controllers\Api\V1\Vendor\VendorRoleController::class)
        ->prefix('roles')
        ->group(function () {
            Route::get('/permissions', 'permissions')->middleware('can:' . \App\Enums\VendorPermission::MANAGE_ROLES->value);
            Route::get('/', 'index')->middleware('can:' . \App\Enums\VendorPermission::MANAGE_ROLES->value);
            Route::post('/', 'store')->middleware('can:' . \App\Enums\VendorPermission::MANAGE_ROLES->value);
            Route::get('/{role}', 'show')->middleware('can:' . \App\Enums\VendorPermission::MANAGE_ROLES->value);
            Route::put('/{role}', 'update')->middleware('can:' . \App\Enums\VendorPermission::MANAGE_ROLES->value);
            Route::delete('/{role}', 'destroy')->middleware('can:' . \App\Enums\VendorPermission::MANAGE_ROLES->value);
        });
});
