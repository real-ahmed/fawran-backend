<?php

namespace App\Notifications\Admin;

use App\Notifications\Concerns\QueuesNotificationDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminCredentialsGenerated extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesNotificationDelivery;

    public function __construct(
        public readonly string $plainTextPassword
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // $loginUrl = config('app.url') . '/admin/login';

        return (new MailMessage)
            ->subject(__('messages.admin_welcome_subject', ['app_name' => config('app.name')]))
            ->greeting(__('messages.hello_name', ['name' => $notifiable->name]))
            ->line(__('messages.admin_account_created'))
            ->line(__('messages.admin_login_credentials'))
            ->line(__('messages.email_label', ['email' => $notifiable->email]))
            ->line(__('messages.password_label', ['password' => $this->plainTextPassword]))
            // ->action(__('Login to Dashboard'), $loginUrl)
            ->line(__('messages.admin_change_password'));
    }
}
