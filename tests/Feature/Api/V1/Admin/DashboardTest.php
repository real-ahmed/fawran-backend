<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\Admin;
use App\Models\Order\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->admin = Admin::factory()->create(['id' => 1]);
    }

    public function test_admin_can_view_dashboard_metrics(): void
    {
        $response = $this->actingAs($this->admin, 'api_admin')
            ->getJson('/api/v1/admin/dashboard/metrics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'orders',
                    'vendors',
                    'couriers',
                ],
                'errors',
            ]);

        $response->assertJsonMissingPath('data.revenue');
    }

    public function test_dashboard_metrics_reflect_live_data(): void
    {
        Order::create([
            'order_type' => 'delivery',
            'total_products' => 100,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin, 'api_admin')
            ->getJson('/api/v1/admin/dashboard/metrics')
            ->assertOk()
            ->assertJsonPath('data.orders.total_products', '100.00');

        Order::create([
            'order_type' => 'delivery',
            'total_products' => 250,
            'status' => 'processing',
        ]);

        $this->actingAs($this->admin, 'api_admin')
            ->getJson('/api/v1/admin/dashboard/metrics')
            ->assertOk()
            ->assertJsonPath('data.orders.total_products', '350.00');
    }

    public function test_admin_can_view_pending_approvals(): void
    {
        $response = $this->actingAs($this->admin, 'api_admin')
            ->getJson('/api/v1/admin/dashboard/pending-approvals');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'brands',
                    'categories',
                    'couriers',
                ],
                'errors',
            ]);
    }
}
