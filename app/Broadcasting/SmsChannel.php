<?php

namespace App\Broadcasting;

use App\Services\Sms\SmsGatewayContract;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    public function __construct(protected SmsGatewayContract $smsGateway) {}

    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $message = $notification->toSms($notifiable);

        // If message is empty or null, don't send
        if (empty($message)) {
            return;
        }

        // Get the phone number from the notifiable model
        $to = null;
        if (method_exists($notifiable, 'routeNotificationForSms')) {
            $to = $notifiable->routeNotificationForSms($notification);
        } else {
            // Default fallback if phone property exists
            $to = $notifiable->phone ?? null;
        }

        if (! $to) {
            return;
        }

        $this->smsGateway->send($to, $message);
    }
}
