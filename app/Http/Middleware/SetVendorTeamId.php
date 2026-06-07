<?php

namespace App\Http\Middleware;

use App\Models\Vendor\Vendor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetVendorTeamId
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            setPermissionsTeamId(0);

            return $next($request);
        }

        // Get all vendor IDs this user has access to (as owner or staff)
        $ownedVendorIds = Vendor::where('owner_id', $user->id)->pluck('id')->toArray();
        $staffVendorIds = $user->vendorStaff()->pluck('vendor_id')->toArray();
        $allowedVendorIds = array_unique(array_merge($ownedVendorIds, $staffVendorIds));

        if (empty($allowedVendorIds)) {
            abort(403, 'You do not belong to any vendor.');
        }

        $requestedVendorId = $request->header('X-VENDOR-ID') ?? $request->query('vendor_id');

        if ($requestedVendorId) {
            // Validate that the requested vendor ID is actually allowed for this user
            if (! in_array((int) $requestedVendorId, $allowedVendorIds)) {
                abort(403, 'Unauthorized access to this vendor.');
            }
            setPermissionsTeamId($requestedVendorId);
        } else {
            // If header is missing, but user only belongs to 1 vendor, auto-select it!
            if (count($allowedVendorIds) === 1) {
                setPermissionsTeamId($allowedVendorIds[0]);
            } else {
                // User has multiple vendors, they MUST specify which one they are managing now
                abort(400, 'Please specify X-VENDOR-ID header as you manage multiple vendors.');
            }
        }

        $selectedVendor = Vendor::query()->find((int) getPermissionsTeamId());

        if (! $selectedVendor?->is_active) {
            abort(403, __('messages.vendor_blocked'));
        }

        return $next($request);
    }
}
