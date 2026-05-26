<?php

namespace App\Services\Sms;

interface SmsGatewayContract
{
    /**
     * Send an SMS message.
     *
     * @param  string  $to  The recipient's phone number
     * @param  string  $message  The message content
     * @return bool True if successful, false otherwise
     */
    public function send(string $to, string $message): bool;
}
