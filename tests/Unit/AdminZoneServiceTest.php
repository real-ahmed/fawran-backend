<?php

namespace Tests\Unit;

use App\Models\Admin;
use App\Models\Geo\DeliveryZone;
use App\Services\Admin\AdminZoneService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdminZoneServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_zone_ids_for_returns_loaded_delivery_zone_keys(): void
    {
        $admin = Admin::factory()->create();
        $zone = $this->createDeliveryZone('Loaded Zone', 0);

        $admin->deliveryZones()->attach($zone);
        $admin->load('deliveryZones');

        $this->assertSame([$zone->id], app(AdminZoneService::class)->zoneIdsFor($admin));
    }

    public function test_restricted_admin_cannot_assign_unowned_delivery_zones(): void
    {
        Admin::factory()->create(['id' => 1]);

        $admin = Admin::factory()->create();
        $ownedZone = $this->createDeliveryZone('Owned Zone', 0);
        $foreignZone = $this->createDeliveryZone('Foreign Zone', 10);
        $admin->deliveryZones()->attach($ownedZone);

        $this->actingAs($admin, 'api_admin');

        $this->expectException(ValidationException::class);

        app(AdminZoneService::class)->ensureZoneIdsAssignable([$ownedZone->id, $foreignZone->id]);
    }

    public function test_super_admin_can_assign_any_delivery_zone(): void
    {
        $superAdmin = Admin::factory()->create(['id' => 1]);
        $foreignZone = $this->createDeliveryZone('Foreign Zone', 0);

        $this->actingAs($superAdmin, 'api_admin');

        app(AdminZoneService::class)->ensureZoneIdsAssignable([$foreignZone->id]);

        $this->assertTrue(true);
    }

    private function createDeliveryZone(string $name, int $offset): DeliveryZone
    {
        $next = $offset + 1;

        $deliveryZone = new DeliveryZone;
        $deliveryZone->name = ['en' => $name, 'ar' => $name];
        $deliveryZone->polygon = DB::raw("ST_GeomFromText('POLYGON(({$offset} {$offset}, {$offset} {$next}, {$next} {$next}, {$next} {$offset}, {$offset} {$offset}))')");
        $deliveryZone->is_active = true;
        $deliveryZone->save();

        return $deliveryZone;
    }
}
