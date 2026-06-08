<?php

namespace Tests\Unit;

use App\Services\Vendor\VendorContextService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class VendorContextServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        setPermissionsTeamId(null);

        parent::tearDown();
    }

    public function test_vendor_id_uses_current_permissions_team_id_first(): void
    {
        setPermissionsTeamId(44);

        $request = Request::create('/vendor', 'GET', ['vendor_id' => 22]);

        $this->assertSame(44, (new VendorContextService)->vendorId($request));
    }

    public function test_vendor_id_falls_back_to_request_context(): void
    {
        setPermissionsTeamId(null);

        $request = Request::create('/vendor', 'GET', ['vendor_id' => 22]);

        $this->assertSame(22, (new VendorContextService)->vendorId($request));
    }

    public function test_vendor_id_requires_request_context_when_team_id_is_missing(): void
    {
        setPermissionsTeamId(null);

        $this->expectException(HttpException::class);

        (new VendorContextService)->vendorId(Request::create('/vendor'));
    }
}
