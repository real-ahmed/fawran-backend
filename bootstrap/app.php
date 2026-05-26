<?php

use App\Http\Middleware\SetVendorTeamId;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: [
            __DIR__.'/../routes/v1/public.php',
            __DIR__.'/../routes/v1/admin.php',
            __DIR__.'/../routes/v1/customer.php',
            __DIR__.'/../routes/v1/courier.php',
            __DIR__.'/../routes/v1/vendor.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'vendor.team' => SetVendorTeamId::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request) {
            return $request->is('api/*');
        });

        // $exceptions->render(function (\Throwable $e, Request $request) {
        //     if ($request->is('api/*')) {
        //         $code = 500;
        //         $message = 'Server Error';
        //         $errors = null;
        //         return response()->json([
        //             'success' => false,
        //             'message' => $message,
        //             'data' => null,
        //             'errors' => $errors,
        //         ], $code);
        //     }
        // });
    })->create();
