<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor\Vendor;
use App\Notifications\Auth\SendPasswordResetOtp;
use App\Services\Auth\PasswordResetService;
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

        if (! $user) {
            return $this->errorResponse('Unauthorized', null, 401);
        }

        // Verify local account exists and password matches
        $localAccount = $user->localAccount;

        if (! $localAccount || ! Hash::check($password, $localAccount->password)) {
            return $this->errorResponse('Unauthorized', null, 401);
        }

        // Generate token
        $token = Auth::guard('api')->login($user);

        return $this->respondWithToken($token);
    }

    public function me()
    {
        $user = Auth::guard('api')->user();
        $user->load(['vendorStaff.vendor', 'addresses']);

        // Let's also attach owned vendors if they are an owner
        $ownedVendors = Vendor::where('owner_id', $user->id)->get();
        $user->setAttribute('owned_vendors', $ownedVendors);

        return $this->successResponse($user, 'Profile retrieved successfully');
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
        $user = Auth::guard('api')->user();
        if ($user) {
            $user->load(['vendorStaff.vendor', 'addresses']);
            $ownedVendors = Vendor::where('owner_id', $user->id)->get();
            $user->setAttribute('owned_vendors', $ownedVendors);
        }

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => $user,
        ], 'Token generated successfully');
    }

    public function forgotPassword(Request $request, PasswordResetService $resetService)
    {
        $request->validate(['login' => 'required|string']);
        $login = $request->input('login');

        $user = User::where('email', $login)->orWhere('phone', $login)->first();

        if (! $user) {
            // Return success anyway to prevent user enumeration
            return $this->successResponse(null, 'If the account exists, an OTP has been sent.');
        }

        $otp = $resetService->generateOtp($login);
        $user->notify(new SendPasswordResetOtp($otp));

        return $this->successResponse(null, 'If the account exists, an OTP has been sent.');
    }

    public function verifyResetOtp(Request $request, PasswordResetService $resetService)
    {
        $request->validate([
            'login' => 'required|string',
            'otp' => 'required|string',
        ]);

        if (! $resetService->verifyOtp($request->input('login'), $request->input('otp'))) {
            return $this->errorResponse('Invalid or expired OTP', null, 400);
        }

        return $this->successResponse(null, 'OTP verified successfully');
    }

    public function resetPassword(Request $request, PasswordResetService $resetService)
    {
        $request->validate([
            'login' => 'required|string',
            'otp' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $login = $request->input('login');

        if (! $resetService->verifyOtp($login, $request->input('otp'))) {
            return $this->errorResponse('Invalid or expired OTP', null, 400);
        }

        $user = User::where('email', $login)->orWhere('phone', $login)->first();

        if ($user && $user->localAccount) {
            $user->localAccount->update([
                'password' => Hash::make($request->input('password')),
            ]);
            $resetService->clearOtp($login);

            return $this->successResponse(null, 'Password has been reset successfully');
        }

        return $this->errorResponse('Account not found', null, 404);
    }
}
