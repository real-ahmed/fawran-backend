<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Shared - Notifications
 *
 * APIs for fetching and managing in-app notifications.
 */
class NotificationController extends Controller
{
    /**
     * List all notifications
     */
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate($request->query('per_page', 15));

        return $this->successResponse($notifications);
    }

    /**
     * List unread notifications
     */
    public function unread(Request $request)
    {
        $notifications = $request->user()->unreadNotifications()->paginate($request->query('per_page', 15));

        return $this->successResponse($notifications);
    }

    /**
     * Mark notification as read
     *
     * Marks a specific notification or all notifications as read.
     *
     * @bodyParam id string optional The ID of the notification. If omitted, marks all as read.
     */
    public function markAsRead(Request $request)
    {
        $id = $request->input('id');

        if ($id) {
            $request->user()->notifications()->where('id', $id)->markAsRead();
        } else {
            $request->user()->unreadNotifications->markAsRead();
        }

        return $this->successResponse(null, 'Notifications marked as read');
    }
}
