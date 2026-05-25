<?php

namespace App\Broadcasting;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;
use Illuminate\Support\Facades\Log;

class FcmChannel
{
    public function __construct(protected Messaging $messaging)
    {
    }

    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toFcm')) {
            return;
        }

        $fcmMessage = $notification->toFcm($notifiable);
        
        if (empty($fcmMessage)) {
            return;
        }

        // Get the device token from the notifiable model
        $token = null;
        if (method_exists($notifiable, 'routeNotificationForFcm')) {
            $token = $notifiable->routeNotificationForFcm($notification);
        } else {
            // Fallback: assume the user has an fcm_token attribute
            $token = $notifiable->fcm_token ?? null;
        }

        if (!$token) {
            return;
        }

        try {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(FcmNotification::create(
                    $fcmMessage['title'] ?? '',
                    $fcmMessage['body'] ?? ''
                ))
                ->withData($fcmMessage['data'] ?? []);

            $this->messaging->send($message);
        } catch (\Exception $e) {
            Log::error("FCM Send failed for {$token}: " . $e->getMessage());
        }
    }
}
