<?php

namespace App\Notifications\Concerns;

use App\Broadcasting\FcmChannel;
use App\Broadcasting\SmsChannel;
use Illuminate\Support\Facades\Log;
use Throwable;

trait QueuesNotificationDelivery
{
    public int $tries = 3;

    public int $timeout = 30;

    public int $maxExceptions = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [1, 5, 10];

    /**
     * @return array<string, string>
     */
    public function viaQueues(): array
    {
        return [
            'mail' => 'notifications',
            'database' => 'notifications',
            'broadcast' => 'notifications',
            SmsChannel::class => 'notifications',
            FcmChannel::class => 'notifications',
        ];
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Queued notification failed.', [
            'notification' => static::class,
            'message' => $exception->getMessage(),
        ]);
    }
}
