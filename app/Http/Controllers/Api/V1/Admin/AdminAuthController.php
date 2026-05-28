<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\Auth\AdminAuthService;
use Illuminate\Http\Request;

/**
 * @group Admin - Authentication
 *
 * APIs for Super Admin authentication.
 */
class AdminAuthController extends Controller
{
    public function __construct(protected AdminAuthService $adminAuthService) {}

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        return $this->adminAuthService->login($credentials);
    }

    public function me()
    {
        return $this->adminAuthService->me();
    }

    public function logout()
    {
        return $this->adminAuthService->logout();
    }

    public function refresh()
    {
        return $this->adminAuthService->refresh();
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        return $this->adminAuthService->forgotPassword($request->input('email'));
    }

    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string',
        ]);

        return $this->adminAuthService->verifyResetOtp($request->input('email'), $request->input('otp'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        return $this->adminAuthService->resetPassword(
            $request->input('email'),
            $request->input('otp'),
            $request->input('password')
        );
    }
}
