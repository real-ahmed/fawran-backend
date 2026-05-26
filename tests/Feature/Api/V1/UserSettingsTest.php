<?php

namespace Tests\Feature\Api\V1;

use App\Http\Middleware\SetLocale;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class UserSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_settings()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->putJson('/api/v1/customer/profile/settings', [
            'settings' => [
                ['key' => 'locale', 'value' => 'en'],
                ['key' => 'theme', 'value' => 'dark'],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertEquals('en', $user->getSetting('locale'));
        $this->assertEquals('dark', $user->getSetting('theme'));
    }

    public function test_admin_can_update_settings()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'api_admin')->putJson('/api/v1/admin/profile/settings', [
            'settings' => [
                ['key' => 'locale', 'value' => 'ar'],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertEquals('ar', $admin->getSetting('locale'));
    }

    public function test_middleware_uses_accept_language_header_for_guest()
    {
        $request = Request::create('/api/test', 'GET');
        $request->headers->set('Accept-Language', 'en');

        $middleware = new SetLocale;
        $middleware->handle($request, function ($req) {
            $this->assertEquals('en', App::getLocale());

            return response('OK');
        });
    }

    public function test_middleware_uses_user_setting_locale()
    {
        $user = User::factory()->create();
        $user->setSetting('locale', 'ar');
        $this->actingAs($user, 'api');

        $request = Request::create('/api/test', 'GET');
        $request->headers->set('Accept-Language', 'en'); // should be ignored

        $middleware = new SetLocale;
        $middleware->handle($request, function ($req) {
            $this->assertEquals('ar', App::getLocale());

            return response('OK');
        });
    }
}
