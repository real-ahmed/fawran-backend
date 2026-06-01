<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\DTOs\Admin\Profile\UpdateAdminProfileDTO;
use App\DTOs\Auth\Login\AdminLoginDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Auth\ForgotPasswordRequest;
use App\Http\Requests\V1\Admin\Auth\LoginRequest;
use App\Http\Requests\V1\Admin\Auth\ResetPasswordRequest;
use App\Http\Requests\V1\Admin\Auth\VerifyResetOtpRequest;
use App\Http\Requests\V1\Admin\Profile\UpdateProfileRequest;
use App\Services\Auth\AdminAuthService;

/**
 * @group Admin - Authentication
 *
 * APIs for Super Admin authentication.
 */
class AdminAuthController extends Controller
{
    public function __construct(protected AdminAuthService $adminAuthService) {}

    public function login(LoginRequest $request)
    {
        $dto = AdminLoginDTO::fromRequest($request);

        return $this->adminAuthService->login($dto);
    }

    public function me()
    {
        return $this->adminAuthService->me();
    }

    public function logout()
    {
        return $this->adminAuthService->logout();
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $dto = UpdateAdminProfileDTO::fromRequest($request);

        return $this->adminAuthService->updateProfile($dto);
    }

    public function refresh()
    {
        return $this->adminAuthService->refresh();
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $data = $request->validated();

        return $this->adminAuthService->forgotPassword($data['email']);
    }

    public function verifyResetOtp(VerifyResetOtpRequest $request)
    {
        $data = $request->validated();

        return $this->adminAuthService->verifyResetOtp($data['email'], $data['otp']);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $data = $request->validated();

        return $this->adminAuthService->resetPassword(
            $data['email'],
            $data['otp'],
            $data['password']
        );
    }
}
