<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserAuthController;

Route::post('customer/login', [UserAuthController::class, 'login']);

Route::middleware('auth:api')->prefix('customer')->group(function () {
    Route::post('logout', [UserAuthController::class, 'logout']);
    Route::post('refresh', [UserAuthController::class, 'refresh']);
    Route::get('me', [UserAuthController::class, 'me']);

    Route::get('test', function () {
        return response()->json(['message' => 'Customer API works!']);
    });
});
