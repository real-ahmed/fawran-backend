<?php

namespace Tests\Feature\Api\V1\Courier;

use App\Models\Courier\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class CourierLocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Redis::del('courier_locations');
    }

    public function test_courier_can_update_location_and_store_in_redis()
    {
        $user = \App\Models\User::factory()->create();
        $courier = Courier::create([
            'user_id' => $user->id,
            'national_id' => '1234567890',
            'plate_number' => 'ABC-1234',
            'vehicle_type' => 'car',
            'is_online' => true,
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/courier/location', [
                'latitude' => 30.0444,
                'longitude' => 31.2357,
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('messages.location_updated_successfully')]);

        $location = Redis::geopos('courier_locations', $courier->id);

        $this->assertNotEmpty($location);
        $this->assertNotEmpty($location[0]);
        // Redis GEO commands store data slightly approximated, so we check near
        $this->assertEqualsWithDelta(31.2357, $location[0][0], 0.001); // Longitude
        $this->assertEqualsWithDelta(30.0444, $location[0][1], 0.001); // Latitude
    }
}
