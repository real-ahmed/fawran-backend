<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = config('app.frontend_api_key');

        if (! $apiKey || $request->header('x-api-key') !== $apiKey) {
            return response()->json([
                'success' => false,
                'message' => __('messages.unauthorized') ?? 'Unauthorized. Invalid API Key.',
            ], 401);
        }

        return $next($request);
    }
}
