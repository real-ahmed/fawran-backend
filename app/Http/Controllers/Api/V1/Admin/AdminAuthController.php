<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group Admin - Authentication
 *
 * APIs for Super Admin authentication.
 */
class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! $token = Auth::guard('api_admin')->attempt($credentials)) {
            return $this->errorResponse('Unauthorized', null, 401);
        }

        return $this->respondWithToken($token);
    }

    public function me()
    {
        return $this->successResponse(Auth::guard('api_admin')->user(), 'Profile retrieved successfully');
    }

    public function logout()
    {
        Auth::guard('api_admin')->logout();

        return $this->successResponse(null, 'Successfully logged out');
    }

    public function refresh()
    {
        return $this->respondWithToken(Auth::guard('api_admin')->refresh());
    }

    protected function respondWithToken($token)
    {
        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60
        ], 'Token generated successfully');
    }
}
