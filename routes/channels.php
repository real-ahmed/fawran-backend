<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:api_admin']]);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.{id}', function ($user, $id) {
    // Only the specific admin can listen to their own channel
    return (int) $user->id === (int) $id;
});
