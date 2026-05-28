<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

/**
 * @group Shared - Notifications
 *
 * APIs for fetching and managing in-app notifications.
 */
class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    /**
     * List all notifications
     */
    public function index(Request $request)
    {
        return $this->successResponse(
            $this->notificationService->list($request->user(), $request->query('per_page', 15))
        );
    }

    /**
     * List unread notifications
     */
    public function unread(Request $request)
    {
        return $this->successResponse(
            $this->notificationService->listUnread($request->user(), $request->query('per_page', 15))
        );
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
        $this->notificationService->markAsRead($request->user(), $request->input('id'));

        return $this->successResponse(null, 'Notifications marked as read');
    }
}
