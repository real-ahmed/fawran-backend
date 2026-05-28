<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Vendor\Vendor;
use App\Notifications\Auth\SendPasswordResetOtp;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserAuthService
{
    use ApiResponser;

    public function __construct(protected PasswordResetService $passwordResetService) {}

    /**
     * @param  array{login: string, password: string}  $credentials
     */
    public function login(array $credentials): JsonResponse
    {
        $user = $this->findUserByLogin($credentials['login']);

        if (! $user || ! $user->localAccount || ! Hash::check($credentials['password'], $user->localAccount->password)) {
            return $this->errorResponse(__('auth.failed'), null, 401);
        }

        return $this->respondWithToken(Auth::guard('api')->login($user));
    }

    public function me(): JsonResponse
    {
        return $this->successResponse(
            $this->profileUser(),
            'Profile retrieved successfully'
        );
    }

    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();

        return $this->successResponse(null, 'Successfully logged out');
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }

    public function forgotPassword(string $login): JsonResponse
    {
        $user = $this->findUserByLogin($login);

        if ($user) {
            $otp = $this->passwordResetService->generateOtp($login);
            $user->notify(new SendPasswordResetOtp($otp));
        }

        return $this->successResponse(null, 'If the account exists, an OTP has been sent.');
    }

    public function verifyResetOtp(string $login, string $otp): JsonResponse
    {
        if (! $this->passwordResetService->verifyOtp($login, $otp)) {
            return $this->errorResponse('Invalid or expired OTP', null, 400);
        }

        return $this->successResponse(null, 'OTP verified successfully');
    }

    public function resetPassword(string $login, string $otp, string $password): JsonResponse
    {
        if (! $this->passwordResetService->verifyOtp($login, $otp)) {
            return $this->errorResponse('Invalid or expired OTP', null, 400);
        }

        $user = $this->findUserByLogin($login);

        if ($user && $user->localAccount) {
            $user->localAccount->update([
                'password' => Hash::make($password),
            ]);
            $this->passwordResetService->clearOtp($login);

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
            'user' => $this->profileUser(),
        ], 'Token generated successfully');
    }

    private function profileUser(): ?User
    {
        $user = Auth::guard('api')->user();

        if (! $user) {
            return null;
        }

        $user->load(['vendorStaff.vendor', 'addresses']);
        $user->setAttribute('owned_vendors', Vendor::where('owner_id', $user->id)->get());

        return $user;
    }

    private function findUserByLogin(string $login): ?User
    {
        return User::where('email', $login)->orWhere('phone', $login)->first();
    }
}
