<?php

namespace Tests\Unit;

use App\Broadcasting\FcmChannel;
use App\Broadcasting\SmsChannel;
use App\Models\User;
use App\Notifications\Admin\AdminCredentialsGeneratedNotification;
use App\Notifications\Auth\SendPasswordResetOtp;
use App\Notifications\Catalog\BrandApprovedNotification;
use App\Notifications\Catalog\CategoryApprovedNotification;
use App\Notifications\Catalog\MasterProductApprovedNotification;
use App\Notifications\CourierApprovedNotification;
use App\Notifications\TestNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class QueuesNotificationDeliveryTest extends TestCase
{
    /**
     * @param  array<int, string>  $arguments
     */
    #[DataProvider('queuedNotificationProvider')]
    public function test_notifications_use_the_notifications_queue(string $notificationClass, array $arguments): void
    {
        $notification = new $notificationClass(...$arguments);

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertInstanceOf(ShouldQueue::class, $notification);
        $this->assertSame(3, $notification->tries);
        $this->assertSame(30, $notification->timeout);
        $this->assertSame(3, $notification->maxExceptions);
        $this->assertSame([1, 5, 10], $notification->backoff);
        $this->assertSame([
            'mail' => 'notifications',
            'database' => 'notifications',
            'broadcast' => 'notifications',
            SmsChannel::class => 'notifications',
            FcmChannel::class => 'notifications',
        ], $notification->viaQueues());
    }

    public function test_password_reset_otp_uses_the_custom_sms_channel_for_users_with_phone_numbers(): void
    {
        $notification = new SendPasswordResetOtp('123456');
        $user = new User(['phone' => '+201000000000']);

        $this->assertSame(['mail', SmsChannel::class], $notification->via($user));
    }

    /**
     * @return array<string, array{notificationClass: class-string, arguments: array<int, string>}>
     */
    public static function queuedNotificationProvider(): array
    {
        return [
            'admin credentials' => [
                'notificationClass' => AdminCredentialsGeneratedNotification::class,
                'arguments' => ['plain-password'],
            ],
            'password reset otp' => [
                'notificationClass' => SendPasswordResetOtp::class,
                'arguments' => ['123456'],
            ],
            'brand approved' => [
                'notificationClass' => BrandApprovedNotification::class,
                'arguments' => ['Acme'],
            ],
            'category approved' => [
                'notificationClass' => CategoryApprovedNotification::class,
                'arguments' => ['Groceries'],
            ],
            'master product approved' => [
                'notificationClass' => MasterProductApprovedNotification::class,
                'arguments' => ['Apples'],
            ],
            'courier approved' => [
                'notificationClass' => CourierApprovedNotification::class,
                'arguments' => ['Courier Name'],
            ],
            'test notification' => [
                'notificationClass' => TestNotification::class,
                'arguments' => ['Title', 'Body'],
            ],
        ];
    }
}
