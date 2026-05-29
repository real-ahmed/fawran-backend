<?php

namespace Tests\Unit;

use App\Http\Requests\V1\Admin\Customer\IndexCustomerRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AdminCustomerRequestTest extends TestCase
{
    public function test_index_customer_request_validates_supported_filters(): void
    {
        $request = new IndexCustomerRequest;

        $validator = Validator::make([
            'search' => str_repeat('a', 256),
            'is_active' => 'maybe',
            'per_page' => 101,
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertSame(['search', 'is_active', 'per_page'], array_keys($validator->errors()->messages()));
    }

    public function test_index_customer_request_normalizes_boolean_filter(): void
    {
        $request = IndexCustomerRequest::create('/api/v1/admin/customers', 'GET', [
            'search' => 'ahmed',
            'is_active' => '0',
            'per_page' => '25',
        ]);
        $request->setContainer($this->app);
        $request->setRedirector($this->app['redirect']);
        $request->setValidator(Validator::make($request->all(), $request->rules()));

        $this->assertSame([
            'search' => 'ahmed',
            'is_active' => false,
            'per_page' => 25,
        ], $request->filters());
    }
}
