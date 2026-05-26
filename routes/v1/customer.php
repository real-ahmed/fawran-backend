<?php

use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\UserAuthController;
use Illuminate\Support\Facades\Route;

Route::post('customer/login', [UserAuthController::class, 'login'])->middleware('throttle:auth');
Route::post('customer/forgot-password', [UserAuthController::class, 'forgotPassword'])->middleware('throttle:auth');
Route::post('customer/verify-reset-otp', [UserAuthController::class, 'verifyResetOtp'])->middleware('throttle:auth');
Route::post('customer/reset-password', [UserAuthController::class, 'resetPassword'])->middleware('throttle:auth');

Route::middleware('auth:api')->prefix('customer')->group(function () {
    Route::post('logout', [UserAuthController::class, 'logout']);
    Route::post('refresh', [UserAuthController::class, 'refresh']);
    Route::get('me', [UserAuthController::class, 'me']);
    Route::put('profile/settings', [SettingsController::class, 'update']);
});
