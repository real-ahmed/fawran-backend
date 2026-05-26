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
            ->subject(__('Password Reset Request'))
            ->greeting(__('Hello!'))
            ->line(__('You are receiving this email because we received a password reset request for your account.'))
            ->line(__('Your password reset code is: :otp', ['otp' => $this->otp]))
            ->line(__('This password reset code will expire in 15 minutes.'))
            ->line(__('If you did not request a password reset, no further action is required.'));
    }

    /**
     * Get the SMS representation of the notification.
     * This relies on the custom SMS channel we built earlier.
     */
    public function toSms(object $notifiable): string
    {
        return __('Your Fawran password reset code is: :otp. It is valid for 15 minutes.', ['otp' => $this->otp]);
    }
}
