<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCourierIsNotBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $courier = $request->user()?->courier;

        if (! $courier || $courier->is_blocked) {
            abort(403, __('messages.courier_blocked'));
        }

        return $next($request);
    }
}
