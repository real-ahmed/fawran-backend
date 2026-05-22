<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('/admin/test', function () {
        return response()->json(['message' => 'Admin API works!']);
    });
});
