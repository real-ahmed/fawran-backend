<?php

namespace Tests\Feature\Finance;

use App\Models\Courier\Courier;
use App\Models\Order\Order;
use App\Models\Payment\CourierCashCollection;
use App\Models\Platform\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SettlementCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SystemSetting::updateOrCreate(['key' => 'settlement_cycle_days'], ['value' => '7', 'group' => 'settlement']);
        SystemSetting::updateOrCreate(['key' => 'courier_cod_wallet_deduction_enabled'], ['value' => 'true', 'group' => 'settlement']);
    }

    public function test_courier_settlement_command(): void
    {
        $courierUser = User::factory()->create();
        $courier = Courier::create(['user_id' => $courierUser->id, 'national_id' => '29001010101010', 'vehicle_type' => 'motorcycle', 'plate_number' => 'S-1', 'is_online' => true]);

        CourierCashCollection::create(['courier_id' => $courier->id, 'source_type' => Order::class, 'source_id' => 1, 'amount_collected' => 200, 'courier_fee_share' => 20, 'amount_owed_to_platform' => 180, 'is_settled' => false, 'collected_at' => now()]);

        $this->artisan('settlement:couriers')->assertExitCode(0);

        $this->assertDatabaseHas('settlements', ['settlement_type' => 'courier', 'target_id' => $courier->id]);
    }

    public function test_no_unsettled_cash_skips_processing(): void
    {
        $this->artisan('settlement:couriers')->assertExitCode(0)->expectsOutputToContain('No unsettled');
    }
}
