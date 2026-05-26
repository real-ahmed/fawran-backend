<?php

namespace Tests\Feature\Api\V1;

use App\Models\Auth\LocalAccount;
use App\Models\User;
use App\Notifications\Auth\SendPasswordResetOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_request_password_reset_otp()
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'test@fawran.test']);

        $response = $this->postJson('/api/v1/customer/forgot-password', [
            'login' => 'test@fawran.test',
        ]);

        $response->assertStatus(200);

        Notification::assertSentTo(
            $user,
            SendPasswordResetOtp::class
        );

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'test@fawran.test',
        ]);
    }

    public function test_can_reset_password_with_valid_otp()
    {
        $user = User::factory()->create(['email' => 'reset@fawran.test']);
        LocalAccount::create([
            'user_id' => $user->id,
            'password' => Hash::make('oldpassword'),
        ]);

        $otp = '123456';
        DB::table('password_reset_tokens')->insert([
            'email' => 'reset@fawran.test',
            'token' => Hash::make($otp),
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/customer/reset-password', [
            'login' => 'reset@fawran.test',
            'otp' => $otp,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200);

        $this->assertTrue(Hash::check('newpassword123', $user->localAccount->fresh()->password));

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'reset@fawran.test',
        ]);
    }
}
