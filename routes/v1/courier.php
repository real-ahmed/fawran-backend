<?php

use App\Http\Controllers\Api\V1\Courier\OrderController;
use App\Http\Controllers\Api\V1\UserAuthController;
use Illuminate\Support\Facades\Route;

Route::post('courier/login', [UserAuthController::class, 'login'])->middleware('throttle:auth');
Route::post('courier/forgot-password', [UserAuthController::class, 'forgotPassword'])->middleware('throttle:auth');
Route::post('courier/verify-reset-otp', [UserAuthController::class, 'verifyResetOtp'])->middleware('throttle:auth');
Route::post('courier/reset-password', [UserAuthController::class, 'resetPassword'])->middleware('throttle:auth');
Route::post('courier/refresh', [UserAuthController::class, 'refresh']);

Route::middleware('auth:api')->prefix('courier')->group(function () {
    Route::post('logout', [UserAuthController::class, 'logout']);
    Route::get('me', [UserAuthController::class, 'me']);

    // Order Endpoints
    Route::post('orders/{order}/accept', [OrderController::class, 'accept']);
});
