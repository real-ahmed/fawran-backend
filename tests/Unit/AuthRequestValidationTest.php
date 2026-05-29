<?php

namespace Tests\Unit;

use App\Http\Requests\V1\Admin\Auth\ForgotPasswordRequest as AdminForgotPasswordRequest;
use App\Http\Requests\V1\Admin\Auth\LoginRequest as AdminLoginRequest;
use App\Http\Requests\V1\Admin\Auth\ResetPasswordRequest as AdminResetPasswordRequest;
use App\Http\Requests\V1\Admin\Auth\VerifyResetOtpRequest as AdminVerifyResetOtpRequest;
use App\Http\Requests\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Http\Requests\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\V1\Auth\VerifyResetOtpRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthRequestValidationTest extends TestCase
{
    /**
     * @param  class-string<FormRequest>  $requestClass
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $expectedErrors
     */
    #[DataProvider('invalidPayloads')]
    public function test_auth_requests_reject_invalid_payloads(string $requestClass, array $payload, array $expectedErrors): void
    {
        $request = new $requestClass;

        $validator = Validator::make($payload, $request->rules());

        $this->assertTrue($request->authorize());
        $this->assertTrue($validator->fails());
        $this->assertSame($expectedErrors, array_keys($validator->errors()->messages()));
    }

    /**
     * @return array<string, array{requestClass: class-string<FormRequest>, payload: array<string, mixed>, expectedErrors: list<string>}>
     */
    public static function invalidPayloads(): array
    {
        return [
            'user login requires credentials' => [
                'requestClass' => LoginRequest::class,
                'payload' => [],
                'expectedErrors' => ['login', 'password'],
            ],
            'user forgot password requires login' => [
                'requestClass' => ForgotPasswordRequest::class,
                'payload' => [],
                'expectedErrors' => ['login'],
            ],
            'user otp verification requires otp' => [
                'requestClass' => VerifyResetOtpRequest::class,
                'payload' => ['login' => 'customer@fawran.test'],
                'expectedErrors' => ['otp'],
            ],
            'user reset password requires confirmation' => [
                'requestClass' => ResetPasswordRequest::class,
                'payload' => [
                    'login' => 'customer@fawran.test',
                    'otp' => '123456',
                    'password' => 'new-password',
                    'password_confirmation' => 'different-password',
                ],
                'expectedErrors' => ['password'],
            ],
            'admin login requires credentials' => [
                'requestClass' => AdminLoginRequest::class,
                'payload' => [],
                'expectedErrors' => ['email', 'password'],
            ],
            'admin forgot password requires email' => [
                'requestClass' => AdminForgotPasswordRequest::class,
                'payload' => [],
                'expectedErrors' => ['email'],
            ],
            'admin otp verification requires valid email and otp' => [
                'requestClass' => AdminVerifyResetOtpRequest::class,
                'payload' => ['email' => 'not-an-email'],
                'expectedErrors' => ['email', 'otp'],
            ],
            'admin reset password requires confirmation' => [
                'requestClass' => AdminResetPasswordRequest::class,
                'payload' => [
                    'email' => 'admin@fawran.test',
                    'otp' => '123456',
                    'password' => 'new-password',
                    'password_confirmation' => 'different-password',
                ],
                'expectedErrors' => ['password'],
            ],
        ];
    }
}
