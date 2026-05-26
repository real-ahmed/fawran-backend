<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\Admin;
use App\Models\Platform\PlatformWallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = Admin::factory()->create();
        PlatformWallet::create(['total_revenue' => 100, 'current_balance' => 50]);
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
                    'revenue',
                ],
            ]);
    }

    public function test_admin_can_view_pending_approvals(): void
    {
        $response = $this->actingAs($this->admin, 'api_admin')
            ->getJson('/api/v1/admin/dashboard/pending-approvals');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'brands',
                    'categories',
                    'couriers',
                ],
            ]);
    }
}
