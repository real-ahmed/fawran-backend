<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserAuthController;

Route::post('courier/login', [UserAuthController::class, 'login']);

Route::middleware('auth:api')->prefix('courier')->group(function () {
    Route::post('logout', [UserAuthController::class, 'logout']);
    Route::post('refresh', [UserAuthController::class, 'refresh']);
    Route::get('me', [UserAuthController::class, 'me']);

    Route::get('test', function () {
        return response()->json(['message' => 'Courier API works!']);
    });
});
