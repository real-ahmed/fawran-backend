<?php

namespace App\Services\Auth;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordResetService
{
    /**
     * Generate a 6-digit numeric OTP and store it in the database.
     */
    public function generateOtp(string $identifier): string
    {
        // Generate a 6-digit OTP
        $otp = (string) random_int(100000, 999999);

        // Delete any existing tokens for this identifier
        DB::table('password_reset_tokens')->where('email', $identifier)->delete();

        // Insert new token (hashed for security)
        DB::table('password_reset_tokens')->insert([
            'email' => $identifier,
            'token' => Hash::make($otp),
            'created_at' => Carbon::now(),
        ]);

        return $otp;
    }

    /**
     * Verify if the provided OTP is valid and not expired (15 minutes).
     */
    public function verifyOtp(string $identifier, string $otp): bool
    {
        $record = DB::table('password_reset_tokens')->where('email', $identifier)->first();

        if (! $record) {
            return false;
        }

        // Check if expired (15 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            return false;
        }

        // Verify the OTP against the hash
        return Hash::check($otp, $record->token);
    }

    /**
     * Delete the OTP record after successful reset.
     */
    public function clearOtp(string $identifier): void
    {
        DB::table('password_reset_tokens')->where('email', $identifier)->delete();
    }
}
