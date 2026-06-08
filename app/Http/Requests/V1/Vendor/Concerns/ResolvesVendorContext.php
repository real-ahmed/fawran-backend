<?php

namespace App\Http\Requests\V1\Vendor\Concerns;

trait ResolvesVendorContext
{
    protected function vendorId(): int
    {
        $vendorId = (int) getPermissionsTeamId();

        if ($vendorId > 0) {
            return $vendorId;
        }

        return (int) ($this->header('X-VENDOR-ID') ?? $this->query('vendor_id'));
    }
}
