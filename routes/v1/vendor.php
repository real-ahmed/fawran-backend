<?php

use App\Enums\VendorPermission;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\UserAuthController;
use App\Http\Controllers\Api\V1\Vendor\VendorRoleController;
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
});
