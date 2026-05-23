<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * @group Shared - Customer / Vendor / Courier Authentication
 *
 * APIs for authenticating standard users (Customers, Vendors, Couriers).
 */
class UserAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string', // can be email or phone
            'password' => 'required|string',
        ]);

        $login = $request->input('login');
        $password = $request->input('password');

        // Find user by email or phone
        $user = User::where('email', $login)->orWhere('phone', $login)->first();

        if (!$user) {
            return $this->errorResponse('Unauthorized', null, 401);
        }

        // Verify local account exists and password matches
        $localAccount = $user->localAccount;

        if (!$localAccount || !Hash::check($password, $localAccount->password)) {
            return $this->errorResponse('Unauthorized', null, 401);
        }

        // Generate token
        $token = Auth::guard('api')->login($user);

        return $this->respondWithToken($token);
    }

    public function me()
    {
        return $this->successResponse(Auth::guard('api')->user(), 'Profile retrieved successfully');
    }

    public function logout()
    {
        Auth::guard('api')->logout();

        return $this->successResponse(null, 'Successfully logged out');
    }

    public function refresh()
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
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
