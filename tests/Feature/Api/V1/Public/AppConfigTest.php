<?php

namespace Tests\Feature\Api\V1\Public;

use App\Models\Admin;
use App\Models\Platform\SystemSetting;
use App\Models\Role;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AppConfigTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_can_fetch_public_app_config(): void
    {
        $response = $this->getJson('/api/v1/public/app-config');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'app_name',
                    'app_icon',
                    'favicon',
                    'app_logo',
                    'app_logo_white',
                    'currency',
                ],
                'errors',
            ]);
    }

    public function test_public_app_config_is_cached_until_system_settings_are_updated(): void
    {
        SystemSetting::where('key', 'currency')->update(['value' => 'EGP']);

        $this->getJson('/api/v1/public/app-config')
            ->assertOk()
            ->assertJsonPath('data.currency', 'EGP');

        SystemSetting::where('key', 'currency')->update(['value' => 'USD']);

        $this->getJson('/api/v1/public/app-config')
            ->assertOk()
            ->assertJsonPath('data.currency', 'EGP');

        $admin = Admin::factory()->create();
        $role = Role::create(['name' => Role::SUPER_ADMIN_NAME, 'guard_name' => 'api_admin']);
        setPermissionsTeamId(0);
        $admin->assignRole($role);

        $this->actingAs($admin, 'api_admin')
            ->putJson('/api/v1/admin/system-settings', [
                'settings' => [
                    ['key' => 'currency', 'value' => 'USD'],
                ],
            ])
            ->assertOk();

        $this->getJson('/api/v1/public/app-config')
            ->assertOk()
            ->assertJsonPath('data.currency', 'USD');
    }
}
