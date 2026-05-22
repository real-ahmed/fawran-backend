<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetVendorTeamId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the store ID from a header (e.g., X-Store-ID) or query parameter.
        // In a real application, you might validate that the user actually belongs to this store.
        $vendorId = $request->header('X-Store-ID') ?? $request->query('vendor_id');

        if ($vendorId) {
            setPermissionsTeamId($vendorId);
        } else {
            // Default to 0 for global/unscoped contexts if no store is specified
            setPermissionsTeamId(0);
        }

        return $next($request);
    }
}
