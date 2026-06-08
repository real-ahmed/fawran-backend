<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Notification\IndexNotificationRequest;
use App\Http\Requests\V1\Notification\MarkNotificationReadRequest;
use App\Services\NotificationService;

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
    public function index(IndexNotificationRequest $request)
    {
        return $this->successResponse(
            $this->notificationService->list($request->user(), $request->validated('per_page', 15))
        );
    }

    /**
     * List unread notifications
     */
    public function unread(IndexNotificationRequest $request)
    {
        return $this->successResponse(
            $this->notificationService->listUnread($request->user(), $request->validated('per_page', 15))
        );
    }

    /**
     * Mark notification as read
     *
     * Marks a specific notification or all notifications as read.
     *
     * @bodyParam id string optional The ID of the notification. If omitted, marks all as read.
     */
    public function markAsRead(MarkNotificationReadRequest $request)
    {
        $this->notificationService->markAsRead($request->user(), $request->validated('id'));

        return $this->successResponse(null, 'Notifications marked as read');
    }
}
