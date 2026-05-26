<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\Admin;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DeliveryZoneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
    }

    protected function authenticateAdmin(): array
    {
        $admin = Admin::firstOrCreate(
            ['email' => 'admin@fawran.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        $token = auth('api_admin')->login($admin);

        return ['Authorization' => 'Bearer '.$token];
    }

    public function test_admin_can_create_delivery_zone(): void
    {
        echo "1. AUTH\n";
        $headers = $this->authenticateAdmin();

        $payload = [
            'name' => ['en' => 'Riyadh Central', 'ar' => 'وسط الرياض'],
            'is_active' => true,
            'coordinates' => [
                ['lat' => 24.711, 'lng' => 46.671],
                ['lat' => 24.715, 'lng' => 46.678],
                ['lat' => 24.708, 'lng' => 46.685],
            ],
        ];

        echo "2. REQUEST\n";
        $response = $this->postJson('/api/v1/admin/delivery-zones', $payload, $headers);

        echo "3. ASSERT STATUS\n";
        $response->assertStatus(201);
        echo "4. ASSERT JSON\n";
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'name' => ['en', 'ar'],
                'geometry' => ['type', 'coordinates'],
            ],
            'errors',
        ]);

        echo "5. ASSERT DB\n";
        $this->assertDatabaseHas('delivery_zones', [
            'is_active' => 1,
        ]);
        echo "6. DONE\n";
    }

    public function test_cannot_create_zone_with_invalid_coordinates(): void
    {
        $headers = $this->authenticateAdmin();
        $payload = [
            'name' => ['en' => 'Test'],
            'coordinates' => [['lat' => 24.711]],
        ];
        $response = $this->postJson('/api/v1/admin/delivery-zones', $payload, $headers);
        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['name.ar', 'coordinates', 'coordinates.0.lng']]);
    }
}
