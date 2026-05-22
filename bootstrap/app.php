<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: [
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
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request) {
            return $request->is('api/*');
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                $code = 500;
                $message = 'Server Error';
                $errors = null;

                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $code = 422;
                    $message = $e->getMessage();
                    $errors = $e->errors();
                } elseif ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException || $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    $code = 404;
                    $message = 'Resource not found';
                } elseif ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    $code = 401;
                    $message = 'Unauthenticated';
                } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    $code = $e->getStatusCode();
                    $message = $e->getMessage() ?: 'Http Error';
                } else {
                    $message = config('app.debug') ? $e->getMessage() : 'Server Error';
                }

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'data' => null,
                    'errors' => $errors,
                ], $code);
            }
        });
    })->create();
