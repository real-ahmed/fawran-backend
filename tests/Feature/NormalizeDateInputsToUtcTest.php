<?php

namespace Tests\Feature;

use App\Http\Middleware\NormalizeDateInputsToUtc;
use App\Models\Model;
use App\Models\Platform\SystemSetting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NormalizeDateInputsToUtcTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_middleware_normalizes_date_time_inputs_from_system_timezone_to_utc(): void
    {
        $this->setSystemTimezone('Africa/Cairo');

        $request = Request::create('/api/test', 'POST', [
            'starts_at' => '2026-06-04 15:30:00',
            'filters' => [
                'to' => '2026-06-04T03:15:00',
            ],
            'name' => '2026-06-04 15:30:00',
            'birthday' => '2026-06-04',
        ]);

        (new NormalizeDateInputsToUtc)->handle($request, function (Request $request) {
            $this->assertSame('2026-06-04 12:30:00', $request->input('starts_at'));
            $this->assertSame('2026-06-04 00:15:00', $request->input('filters.to'));
            $this->assertSame('2026-06-04 15:30:00', $request->input('name'));
            $this->assertSame('2026-06-04', $request->input('birthday'));

            return response('OK');
        });
    }

    public function test_middleware_converts_explicit_timezone_offsets_to_utc(): void
    {
        $this->setSystemTimezone('Africa/Cairo');

        $request = Request::create('/api/test', 'POST', [
            'expires_at' => '2026-06-04T15:30:00+02:00',
        ]);

        (new NormalizeDateInputsToUtc)->handle($request, function (Request $request) {
            $this->assertSame('2026-06-04 13:30:00', $request->input('expires_at'));

            return response('OK');
        });
    }

    public function test_middleware_normalizes_query_and_body_input_separately(): void
    {
        $this->setSystemTimezone('Africa/Cairo');

        $request = Request::create('/api/test?created_at=2026-06-04%2015:30:00', 'POST', [
            'starts_at' => '2026-06-04 15:30:00',
        ]);

        (new NormalizeDateInputsToUtc)->handle($request, function (Request $request) {
            $this->assertSame('2026-06-04 12:30:00', $request->request->get('starts_at'));
            $this->assertSame('2026-06-04 12:30:00', $request->query->get('created_at'));
            $this->assertFalse($request->request->has('created_at'));

            return response('OK');
        });
    }

    public function test_normalized_date_time_input_is_persisted_as_utc(): void
    {
        $this->setSystemTimezone('Africa/Cairo');
        $this->createTimezoneTestEventsTable();

        $request = Request::create('/api/test', 'POST', [
            'scheduled_at' => '2026-06-04 15:30:00',
        ]);

        (new NormalizeDateInputsToUtc)->handle($request, function (Request $request) {
            (new TimezoneTestEvent)->forceFill([
                'scheduled_at' => $request->input('scheduled_at'),
            ])->save();

            return response('OK');
        });

        $this->assertDatabaseHas('timezone_test_events', [
            'scheduled_at' => '2026-06-04 12:30:00',
        ]);
    }

    private function setSystemTimezone(string $timezone): void
    {
        SystemSetting::query()->updateOrCreate(
            ['key' => 'timezone'],
            ['value' => $timezone, 'group' => 'general']
        );
        SystemSetting::flushCachedValues(['timezone']);
    }

    private function createTimezoneTestEventsTable(): void
    {
        Schema::dropIfExists('timezone_test_events');
        Schema::create('timezone_test_events', function (Blueprint $table): void {
            $table->id();
            $table->dateTime('scheduled_at')->nullable();
        });
    }
}

class TimezoneTestEvent extends Model
{
    protected $table = 'timezone_test_events';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }
}
