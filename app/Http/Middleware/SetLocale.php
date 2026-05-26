<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        // 1. Check if user is authenticated (via any guard) and has a locale setting
        $user = auth()->user() ?? auth('api_admin')->user();
        if ($user && method_exists($user, 'getSetting')) {
            $locale = $user->getSetting('locale');
        }

        // 2. If no user locale, fallback to Accept-Language header
        if (! $locale && $request->hasHeader('Accept-Language')) {
            $headerLocale = strtolower(substr($request->header('Accept-Language'), 0, 2));
            if (in_array($headerLocale, ['ar', 'en'])) {
                $locale = $headerLocale;
            }
        }

        // 3. Fallback to default app locale, then set it
        app()->setLocale($locale ?: config('app.locale'));

        return $next($request);
    }
}
