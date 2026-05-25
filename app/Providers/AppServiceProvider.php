<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Services\Sms\SmsGatewayContract::class,
            function ($app) {
                // Return LogSmsGateway in local/testing, else use real provider
                if ($app->environment('local', 'testing')) {
                    return new \App\Services\Sms\LogSmsGateway();
                }
                
                return new \App\Services\Sms\SmsMisrGateway();
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Implicitly grant "Super Admin" role all permissions
        // This avoids having to sync hundreds of permissions in the database
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // Global API rate limiting
        \Illuminate\Support\Facades\RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
            $limit = config('api.rate_limits.api', 100);
            return \Illuminate\Cache\RateLimiting\Limit::perMinute($limit)->by($request->user()?->id ?: $request->ip());
        });

        // Strict rate limiting for Authentication endpoints
        \Illuminate\Support\Facades\RateLimiter::for('auth', function (\Illuminate\Http\Request $request) {
            $limit = config('api.rate_limits.auth', 5);
            return \Illuminate\Cache\RateLimiting\Limit::perMinute($limit)->by($request->ip());
        });
    }
}
