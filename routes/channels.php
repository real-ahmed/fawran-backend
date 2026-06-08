<?php

use App\Models\Vendor\Vendor;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:api,api_admin']]);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.{id}', function ($user, $id) {
    // Only the specific admin can listen to their own channel
    return (int) $user->id === (int) $id;
}, ['guards' => ['api_admin']]);

Broadcast::channel('vendor.{id}', function ($user, $id) {
    // Check if user is staff of this vendor (via pivot or ownership)
    return $user->vendors()->where('vendors.id', $id)->exists() ||
           Vendor::where('id', $id)->where('owner_id', $user->id)->exists();
}, ['guards' => ['api']]);
