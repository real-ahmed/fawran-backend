<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsMisrGateway implements SmsGatewayContract
{
    /**
     * Send SMS via SMS Misr API
     */
    public function send(string $to, string $message): bool
    {
        $environment = config('services.smsmisr.environment', '1'); // 1=live, 2=test
        $username = config('services.smsmisr.username');
        $password = config('services.smsmisr.password');
        $sender = config('services.smsmisr.sender');

        if (! $username || ! $password) {
            Log::error('SMS Misr credentials not configured.');

            return false;
        }

        try {
            $response = Http::post('https://smsmisr.com/api/webapi/', [
                'environment' => $environment,
                'username' => $username,
                'password' => $password,
                'sender' => $sender,
                'mobile' => $to,
                'message' => $message,
            ]);

            if ($response->successful()) {
                // SMS Misr usually returns a specific code on success
                return true;
            }

            Log::error("SMS Misr failed to send to {$to}: ".$response->body());

            return false;
        } catch (\Exception $e) {
            Log::error('SMS Misr Exception: '.$e->getMessage());

            return false;
        }
    }
}
