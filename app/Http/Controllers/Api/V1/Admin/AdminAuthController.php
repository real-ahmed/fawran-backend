<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Notifications\Auth\SendPasswordResetOtp;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
            'expires_in' => config('jwt.ttl') * 60,
        ], 'Token generated successfully');
    }

    public function forgotPassword(Request $request, PasswordResetService $resetService)
    {
        $request->validate(['email' => 'required|email']);
        $email = $request->input('email');

        $admin = Admin::where('email', $email)->first();

        if (! $admin) {
            return $this->successResponse(null, 'If the account exists, an OTP has been sent.');
        }

        $otp = $resetService->generateOtp($email);
        $admin->notify(new SendPasswordResetOtp($otp));

        return $this->successResponse(null, 'If the account exists, an OTP has been sent.');
    }

    public function verifyResetOtp(Request $request, PasswordResetService $resetService)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string',
        ]);

        if (! $resetService->verifyOtp($request->input('email'), $request->input('otp'))) {
            return $this->errorResponse('Invalid or expired OTP', null, 400);
        }

        return $this->successResponse(null, 'OTP verified successfully');
    }

    public function resetPassword(Request $request, PasswordResetService $resetService)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = $request->input('email');

        if (! $resetService->verifyOtp($email, $request->input('otp'))) {
            return $this->errorResponse('Invalid or expired OTP', null, 400);
        }

        $admin = Admin::where('email', $email)->first();

        if ($admin) {
            $admin->update([
                'password' => Hash::make($request->input('password')),
            ]);
            $resetService->clearOtp($email);

            return $this->successResponse(null, 'Password has been reset successfully');
        }

        return $this->errorResponse('Account not found', null, 404);
    }
}
