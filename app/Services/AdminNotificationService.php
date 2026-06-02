<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class AdminNotificationService
{
    /**
     * Send a notification to all admins who have a specific permission.
     *
     * @param  string  $permission  The required permission name.
     * @param  Notification  $notification  The notification instance to send.
     * @param  Collection|null  $adminsSubset  Optional subset of admins to filter from.
     */
    public function notifyAdminsWithPermission(string $permission, Notification $notification, ?Collection $adminsSubset = null): void
    {
        if ($adminsSubset) {
            $admins = $adminsSubset->filter(fn ($admin) => $admin->isSuperAdmin() || $admin->hasPermissionTo($permission));
        } else {
            $admins = Admin::query()
                ->superAdmins()
                ->orWhereHas('permissions', fn ($q) => $q->where('name', $permission))
                ->get();
        }

        if ($admins->isNotEmpty()) {
            NotificationFacade::send($admins, $notification);
        }
    }
}
