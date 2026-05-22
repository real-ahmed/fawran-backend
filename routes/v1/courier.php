<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('/courier/test', function () {
        return response()->json(['message' => 'Courier API works!']);
    });
});
