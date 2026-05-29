<?php

namespace Tests\Unit;

use App\Http\Resources\V1\Admin\CourierResource;
use App\Models\Courier\Courier;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class CourierResourceTest extends TestCase
{
    public function test_loaded_missing_document_is_serialized_as_null(): void
    {
        $courier = new Courier([
            'vehicle_type' => 'motorcycle',
            'plate_number' => 'ABC-123',
            'is_online' => false,
        ]);

        $courier->setRelation('document', null);

        $data = (new CourierResource($courier))->toArray(Request::create('/'));

        $this->assertArrayHasKey('document', $data);
        $this->assertNull($data['document']);
    }
}
