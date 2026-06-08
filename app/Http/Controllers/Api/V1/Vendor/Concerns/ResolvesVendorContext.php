<?php

namespace App\Http\Controllers\Api\V1\Vendor\Concerns;

use App\Models\Vendor\Vendor;
use App\Services\Vendor\VendorContextService;
use Illuminate\Http\Request;

trait ResolvesVendorContext
{
    protected function vendorId(Request $request): int
    {
        return $this->vendorContextService()->vendorId($request);
    }

    protected function vendor(Request $request): Vendor
    {
        return $this->vendorContextService()->vendor($request);
    }

    private function vendorContextService(): VendorContextService
    {
        return app(VendorContextService::class);
    }
}
