<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\Courier\Courier;
use App\Models\Geo\DeliveryZone;
use App\Models\Platform\SystemSetting;
use App\Models\User;
use App\Services\Admin\CourierService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CourierContractDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_creates_contract_document_with_required_fields(): void
    {
        $courier = Courier::create([
            'user_id' => User::factory()->create()->id,
            'delivery_zone_id' => $this->createDeliveryZone()->id,
            'vehicle_type' => 'motorcycle',
            'plate_number' => 'DOC-100',
            'national_id' => '1234567890',
            'is_online' => false,
        ]);

        $document = app(CourierService::class)->ensureContractDocument($courier);

        $this->assertSame($courier->id, $document->courier_id);
        $this->assertSame('', $document->criminal_record_file);
        $this->assertSame('CTR-'.$courier->id.'-'.date('Ym'), $document->contract_number);
        $this->assertDatabaseHas('courier_documents', [
            'courier_id' => $courier->id,
            'criminal_record_file' => '',
            'contract_number' => $document->contract_number,
        ]);
    }

    public function test_service_builds_contract_view_data(): void
    {
        SystemSetting::updateOrCreate(
            ['key' => 'courier_contract_template'],
            [
                'value' => '{contract_number}|{day_name}|{courier_name}|{phone}|{vehicle_type}',
                'group' => 'general',
            ]
        );

        SystemSetting::updateOrCreate(
            ['key' => 'app_name'],
            [
                'value' => '{"ar":"فورا","en":"Fawran"}',
                'group' => 'general',
            ]
        );

        $user = User::factory()->create([
            'name' => 'Ahmed Emad',
            'phone' => '01090000001',
        ]);

        $courier = Courier::create([
            'user_id' => $user->id,
            'delivery_zone_id' => $this->createDeliveryZone()->id,
            'vehicle_type' => 'motorcycle',
            'plate_number' => 'DOC-101',
            'national_id' => '1234567891',
            'is_online' => false,
        ]);

        $contract = app(CourierService::class)->getContractViewData($courier);

        $this->assertIsArray($contract);
        $this->assertSame('Fawran', $contract['appName']);
        $this->assertSame('CTR-'.$courier->id.'-'.date('Ym'), $contract['contractNumber']);
        $this->assertStringContainsString(__('messages.days.'.date('l')), $contract['html']);
        $this->assertStringContainsString('Ahmed Emad', $contract['html']);
        $this->assertStringContainsString('01090000001', $contract['html']);
    }

    private function createDeliveryZone(): DeliveryZone
    {
        $deliveryZone = new DeliveryZone;
        $deliveryZone->name = ['en' => 'Test Zone', 'ar' => 'منطقة اختبار'];
        $deliveryZone->polygon = DB::raw("ST_GeomFromText('POLYGON((46.671 24.711,46.678 24.715,46.685 24.708,46.671 24.711))')");
        $deliveryZone->is_active = true;
        $deliveryZone->save();

        return $deliveryZone;
    }
}
