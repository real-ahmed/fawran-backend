<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Pagination\CursorPaginator;

class NotificationService
{
    public function list(Authenticatable $user, int|string $perPage = 15): CursorPaginator
    {
        return $user
            ->notifications()
            ->latest('created_at')
            ->latest('id')
            ->cursorPaginate($perPage);
    }

    public function listUnread(Authenticatable $user, int|string $perPage = 15): CursorPaginator
    {
        return $user
            ->unreadNotifications()
            ->latest('created_at')
            ->latest('id')
            ->cursorPaginate($perPage);
    }

    public function markAsRead(Authenticatable $user, ?string $id = null): void
    {
        if ($id) {
            $user->notifications()->where('id', $id)->markAsRead();

            return;
        }

        $user->unreadNotifications->markAsRead();
    }
}
