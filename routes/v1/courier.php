<?php

use App\Http\Controllers\Api\V1\UserAuthController;
use Illuminate\Support\Facades\Route;

Route::post('courier/login', [UserAuthController::class, 'login'])->middleware('throttle:auth');
Route::post('courier/forgot-password', [UserAuthController::class, 'forgotPassword'])->middleware('throttle:auth');
Route::post('courier/verify-reset-otp', [UserAuthController::class, 'verifyResetOtp'])->middleware('throttle:auth');
Route::post('courier/reset-password', [UserAuthController::class, 'resetPassword'])->middleware('throttle:auth');

Route::middleware('auth:api')->prefix('courier')->group(function () {
    Route::post('logout', [UserAuthController::class, 'logout']);
    Route::post('refresh', [UserAuthController::class, 'refresh']);
    Route::get('me', [UserAuthController::class, 'me']);
});
