<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

class LogSmsGateway implements SmsGatewayContract
{
    /**
     * Log the SMS message instead of sending it. Useful for local development.
     */
    public function send(string $to, string $message): bool
    {
        Log::info("SMS Mock sent to [{$to}]: {$message}");
        return true;
    }
}
