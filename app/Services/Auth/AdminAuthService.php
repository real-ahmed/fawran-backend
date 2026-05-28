<?php

namespace App\Services\Auth;

use App\Http\Resources\V1\AdminResource;
use App\Models\Admin;
use App\Notifications\Auth\SendPasswordResetOtp;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthService
{
    use ApiResponser;

    public function __construct(protected PasswordResetService $passwordResetService) {}

    /**
     * @param  array{email: string, password: string}  $credentials
     */
    public function login(array $credentials): JsonResponse
    {
        if (! $token = Auth::guard('api_admin')->attempt($credentials)) {
            return $this->errorResponse(__('auth.failed'), null, 401);
        }

        return $this->respondWithToken($token);
    }

    public function me(): JsonResponse
    {
        return $this->successResponse(new AdminResource(Auth::guard('api_admin')->user()), 'Profile retrieved successfully');
    }

    public function logout(): JsonResponse
    {
        Auth::guard('api_admin')->logout();

        return $this->successResponse(null, 'Successfully logged out');
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(Auth::guard('api_admin')->refresh());
    }

    public function forgotPassword(string $email): JsonResponse
    {
        $admin = Admin::where('email', $email)->first();

        if ($admin) {
            $otp = $this->passwordResetService->generateOtp($email);
            $admin->notify(new SendPasswordResetOtp($otp));
        }

        return $this->successResponse(null, 'If the account exists, an OTP has been sent.');
    }

    public function verifyResetOtp(string $email, string $otp): JsonResponse
    {
        if (! $this->passwordResetService->verifyOtp($email, $otp)) {
            return $this->errorResponse('Invalid or expired OTP', null, 400);
        }

        return $this->successResponse(null, 'OTP verified successfully');
    }

    public function resetPassword(string $email, string $otp, string $password): JsonResponse
    {
        if (! $this->passwordResetService->verifyOtp($email, $otp)) {
            return $this->errorResponse('Invalid or expired OTP', null, 400);
        }

        $admin = Admin::where('email', $email)->first();

        if ($admin) {
            $admin->update([
                'password' => Hash::make($password),
            ]);
            $this->passwordResetService->clearOtp($email);

            return $this->successResponse(null, 'Password has been reset successfully');
        }

        return $this->errorResponse('Account not found', null, 404);
    }

    private function respondWithToken(string $token): JsonResponse
    {
        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ], 'Token generated successfully');
    }
}
