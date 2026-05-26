<?php

namespace Tests\Feature\Api\V1\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_public_app_config()
    {
        // First we seed the branding settings via running migrations.
        // The migration should have inserted app_icon and favicon.
        // We can also insert an app_name just to be sure.
        \App\Models\Platform\SystemSetting::create([
            'key' => 'app_name',
            'value' => '{"en": "Fawran", "ar": "فوران"}',
            'group' => 'branding'
        ]);

        $response = $this->getJson('/api/v1/public/app-config');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'app_name',
                    'app_icon',
                    'favicon'
                ]
            ]);
    }
}
