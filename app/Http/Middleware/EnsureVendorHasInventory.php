<?php

namespace App\Http\Middleware;

use App\Models\Vendor\Vendor;
use App\Services\Vendor\VendorTypeRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorHasInventory
{
    public function __construct(
        private VendorTypeRegistry $registry
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $vendorId = (int) getPermissionsTeamId();

        if ($vendorId <= 0) {
            $vendorId = (int) ($request->header('X-VENDOR-ID') ?? $request->query('vendor_id'));
        }

        abort_if($vendorId <= 0, 400, 'Vendor ID is required.');

        $vendor = Vendor::findOrFail($vendorId);

        $handler = $this->registry->handler($vendor->type);

        abort_unless($handler->hasInventory(), 403, 'This vendor type does not support inventory management.');

        return $next($request);
    }
}
