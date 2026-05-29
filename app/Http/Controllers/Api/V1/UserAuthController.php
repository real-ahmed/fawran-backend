<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Auth\Login\LoginDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Http\Requests\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\V1\Auth\VerifyResetOtpRequest;
use App\Services\Auth\UserAuthService;

/**
 * @group Shared - Customer / Vendor / Courier Authentication
 *
 * APIs for authenticating standard users (Customers, Vendors, Couriers).
 */
class UserAuthController extends Controller
{
    public function __construct(protected UserAuthService $userAuthService) {}

    public function login(LoginRequest $request)
    {
        $dto = LoginDTO::fromRequest($request);

        return $this->userAuthService->login($dto);
    }

    public function me()
    {
        return $this->userAuthService->me();
    }

    public function logout()
    {
        return $this->userAuthService->logout();
    }

    public function refresh()
    {
        return $this->userAuthService->refresh();
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $data = $request->validated();

        return $this->userAuthService->forgotPassword($data['login']);
    }

    public function verifyResetOtp(VerifyResetOtpRequest $request)
    {
        $data = $request->validated();

        return $this->userAuthService->verifyResetOtp($data['login'], $data['otp']);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $data = $request->validated();

        return $this->userAuthService->resetPassword(
            $data['login'],
            $data['otp'],
            $data['password']
        );
    }
}
