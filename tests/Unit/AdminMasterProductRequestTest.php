<?php

namespace Tests\Unit;

use App\Http\Requests\V1\Admin\MasterProduct\RejectMasterProductRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AdminMasterProductRequestTest extends TestCase
{
    public function test_reject_master_product_request_accepts_optional_reason(): void
    {
        $request = new RejectMasterProductRequest;

        $validator = Validator::make([], $request->rules());

        $this->assertTrue($request->authorize());
        $this->assertFalse($validator->fails());
    }

    public function test_reject_master_product_request_limits_reason_length(): void
    {
        $request = new RejectMasterProductRequest;

        $validator = Validator::make([
            'reason' => str_repeat('a', 256),
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertSame(['reason'], array_keys($validator->errors()->messages()));
    }
}
