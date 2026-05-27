<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Auth\LocalAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_login_with_wrong_password_returns_credentials_message(): void
    {
        $user = User::factory()->create(['email' => 'customer@fawran.test']);
        LocalAccount::create([
            'user_id' => $user->id,
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->postJson('/api/v1/customer/login', [
            'login' => 'customer@fawran.test',
            'password' => 'wrong-password',
        ], [
            'Accept-Language' => 'en',
        ]);

        $response
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', __('auth.failed', locale: 'en'))
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors', null);
    }

    public function test_customer_login_with_unknown_account_returns_credentials_message(): void
    {
        $response = $this->postJson('/api/v1/customer/login', [
            'login' => 'missing@fawran.test',
            'password' => 'wrong-password',
        ], [
            'Accept-Language' => 'ar',
        ]);

        $response
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', __('auth.failed', locale: 'ar'))
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors', null);
    }

    public function test_admin_login_with_wrong_password_returns_credentials_message(): void
    {
        Admin::factory()->create([
            'email' => 'admin@fawran.test',
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@fawran.test',
            'password' => 'wrong-password',
        ], [
            'Accept-Language' => 'en',
        ]);

        $response
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', __('auth.failed', locale: 'en'))
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors', null);
    }

    public function test_admin_login_with_unknown_account_returns_credentials_message(): void
    {
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'missing@fawran.test',
            'password' => 'wrong-password',
        ], [
            'Accept-Language' => 'ar',
        ]);

        $response
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', __('auth.failed', locale: 'ar'))
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors', null);
    }
}
