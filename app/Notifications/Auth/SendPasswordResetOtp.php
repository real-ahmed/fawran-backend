<?php

namespace App\Notifications\Auth;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendPasswordResetOtp extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly string $otp
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Admin doesn't have a phone number, User does.
        $channels = ['mail'];

        // If it's a User model and has a phone number, send SMS too
        if ($notifiable instanceof User && ! empty($notifiable->phone)) {
            $channels[] = 'sms';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('messages.password_reset_request_subject'))
            ->greeting(__('messages.hello'))
            ->line(__('messages.password_reset_request_reason'))
            ->line(__('messages.password_reset_code_is', ['otp' => $this->otp]))
            ->line(__('messages.password_reset_code_expire'))
            ->line(__('messages.password_reset_no_action'));
    }

    public function toSms(object $notifiable): string
    {
        $setting = \App\Models\Platform\SystemSetting::where('key', 'app_name')->first();
        $appName = 'Fawran';
        
        if ($setting && $setting->value) {
            $decoded = json_decode($setting->value, true);
            $locale = app()->getLocale();
            if (is_array($decoded) && isset($decoded[$locale])) {
                $appName = $decoded[$locale];
            } else {
                $appName = $setting->value;
            }
        }

        return __('messages.password_reset_sms', [
            'app_name' => $appName,
            'otp' => $this->otp
        ]);
    }
}
