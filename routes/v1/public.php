<?php

use App\Http\Controllers\Api\V1\Public\AppConfigController;
use App\Http\Middleware\ValidateApiKey;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function () {
    Route::get('/app-config', [AppConfigController::class, 'index']);

    Route::middleware([ValidateApiKey::class])->group(function () {
        Route::get('/admin-permissions', [AppConfigController::class, 'adminPermissions']);
        Route::get('/vendor-permissions', [AppConfigController::class, 'vendorPermissions']);
    });
});
