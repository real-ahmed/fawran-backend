<?php

namespace App\Notifications;

use App\Broadcasting\FcmChannel;
use App\Broadcasting\SmsChannel;
use App\Notifications\Concerns\QueuesNotificationDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TestNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesNotificationDelivery;

    public function __construct(public string $messageTitle, public string $messageBody) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail', SmsChannel::class, FcmChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->messageTitle)
            ->line($this->messageBody)
            ->action(__('messages.open_app'), url('/'))
            ->line(__('messages.thank_you_for_using'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->messageTitle,
            'body' => $this->messageBody,
            'type' => 'system_alert',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     * This is used for real-time WebSockets (Pusher/Reverb) for Web Apps.
     */
    public function toBroadcast(object $notifiable)
    {
        return new BroadcastMessage([
            'title' => $this->messageTitle,
            'body' => $this->messageBody,
            'type' => 'system_alert',
        ])->onQueue('notifications');
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): string
    {
        return "{$this->messageTitle}: {$this->messageBody}";
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm(object $notifiable): array
    {
        return [
            'title' => $this->messageTitle,
            'body' => $this->messageBody,
            'data' => [
                'type' => 'system_alert',
            ],
        ];
    }
}
