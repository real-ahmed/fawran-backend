<?php

namespace App\Services\Vendor;

use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;

class VendorContextService
{
    public function vendorId(Request $request): int
    {
        $vendorId = (int) getPermissionsTeamId();

        if ($vendorId > 0) {
            return $vendorId;
        }

        $vendorId = (int) ($request->header('X-VENDOR-ID') ?? $request->query('vendor_id'));

        abort_if($vendorId <= 0, 400, 'Vendor ID is required.');

        return $vendorId;
    }

    public function vendor(Request $request): Vendor
    {
        return Vendor::query()->findOrFail($this->vendorId($request));
    }
}
