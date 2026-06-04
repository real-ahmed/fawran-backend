<?php

namespace Tests\Unit;

use App\Models\Model;
use App\Models\Platform\SystemSetting;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SystemTimezoneDatesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_model_dates_are_serialized_with_the_system_timezone(): void
    {
        SystemSetting::query()->updateOrCreate(
            ['key' => 'timezone'],
            ['value' => 'Africa/Cairo', 'group' => 'general']
        );
        SystemSetting::flushCachedValues(['timezone']);

        $model = new SystemTimezoneDateTestModel;
        $model->forceFill([
            'scheduled_at' => CarbonImmutable::parse('2026-06-04 12:30:00', 'UTC'),
        ]);

        $this->assertSame('2026-06-04T15:30:00+03:00', $model->toArray()['scheduled_at']);
    }

    public function test_carbon_dates_returned_directly_from_resources_are_serialized_with_the_system_timezone(): void
    {
        SystemSetting::query()->updateOrCreate(
            ['key' => 'timezone'],
            ['value' => 'Africa/Cairo', 'group' => 'general']
        );
        SystemSetting::flushCachedValues(['timezone']);

        $payload = json_decode(json_encode([
            'created_at' => CarbonImmutable::parse('2026-06-04 12:30:00', 'UTC'),
        ]), true);

        $this->assertSame('2026-06-04T15:30:00+03:00', $payload['created_at']);
    }
}

class SystemTimezoneDateTestModel extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }
}
