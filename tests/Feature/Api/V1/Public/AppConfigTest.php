<?php

namespace Tests\Feature\Api\V1\Public;

use App\Models\Platform\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_public_app_config()
    {


        $response = $this->getJson('/api/v1/public/app-config');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'app_name',
                    'app_icon',
                    'favicon',
                    'app_logo',
                    'app_logo_white'
                ],
            ]);
    }
}
