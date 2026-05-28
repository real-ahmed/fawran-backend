<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Auth\UserAuthService;
use Illuminate\Http\Request;

/**
 * @group Shared - Customer / Vendor / Courier Authentication
 *
 * APIs for authenticating standard users (Customers, Vendors, Couriers).
 */
class UserAuthController extends Controller
{
    public function __construct(protected UserAuthService $userAuthService) {}

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string', // can be email or phone
            'password' => 'required|string',
        ]);

        return $this->userAuthService->login($credentials);
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

    public function forgotPassword(Request $request)
    {
        $request->validate(['login' => 'required|string']);

        return $this->userAuthService->forgotPassword($request->input('login'));
    }

    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'otp' => 'required|string',
        ]);

        return $this->userAuthService->verifyResetOtp($request->input('login'), $request->input('otp'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'otp' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        return $this->userAuthService->resetPassword(
            $request->input('login'),
            $request->input('otp'),
            $request->input('password')
        );
    }
}
