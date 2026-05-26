<?php

use App\Http\Controllers\Api\V1\Public\AppConfigController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function () {
    Route::get('/app-config', [AppConfigController::class, 'index']);
    Route::get('/admin-permissions', [AppConfigController::class, 'adminPermissions']);
});
