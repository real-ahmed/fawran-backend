<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAdminTeamId
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        setPermissionsTeamId(0);

        if ($user = $request->user()) {
            $user->unsetRelation('roles')->unsetRelation('permissions');
        }

        return $next($request);
    }
}
