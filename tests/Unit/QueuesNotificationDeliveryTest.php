<?php

namespace Tests\Unit;

use App\Broadcasting\FcmChannel;
use App\Broadcasting\SmsChannel;
use App\Models\User;
use App\Notifications\Admin\AdminCredentialsGenerated;
use App\Notifications\Auth\SendPasswordResetOtp;
use App\Notifications\CatalogItemApproved;
use App\Notifications\CourierApproved;
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
                'notificationClass' => AdminCredentialsGenerated::class,
                'arguments' => ['plain-password'],
            ],
            'password reset otp' => [
                'notificationClass' => SendPasswordResetOtp::class,
                'arguments' => ['123456'],
            ],
            'catalog item approved' => [
                'notificationClass' => CatalogItemApproved::class,
                'arguments' => ['Brand', 'Acme'],
            ],
            'courier approved' => [
                'notificationClass' => CourierApproved::class,
                'arguments' => ['Courier Name'],
            ],
            'test notification' => [
                'notificationClass' => TestNotification::class,
                'arguments' => ['Title', 'Body'],
            ],
        ];
    }
}
