<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Admin\AdminAuthController;

Route::post('admin/login', [AdminAuthController::class, 'login']);

Route::middleware('auth:api_admin')->prefix('admin')->group(function () {
    Route::post('logout', [AdminAuthController::class, 'logout']);
    Route::post('refresh', [AdminAuthController::class, 'refresh']);
    Route::get('me', [AdminAuthController::class, 'me']);

    Route::get('test', function () {
        return response()->json(['message' => 'Admin API works!']);
    });
});
