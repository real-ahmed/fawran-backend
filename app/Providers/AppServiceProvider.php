<?php

namespace App\Providers;

use App\Services\Sms\LogSmsGateway;
use App\Services\Sms\SmsGatewayContract;
use App\Services\Sms\SmsMisrGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            SmsGatewayContract::class,
            function ($app) {
                // Return LogSmsGateway in local/testing, else use real provider
                if ($app->environment('local', 'testing')) {
                    return new LogSmsGateway;
                }

                return new SmsMisrGateway;
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        
        \App\Models\Courier\Courier::observe(\App\Observers\Courier\CourierObserver::class);

        // Implicitly grant "Super Admin" role all permissions
        // This avoids having to sync hundreds of permissions in the database
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // Global API rate limiting
        RateLimiter::for('api', function (Request $request) {
            $limit = config('api.rate_limits.api', 100);

            return Limit::perMinute($limit)->by($request->user()?->id ?: $request->ip());
        });

        // Strict rate limiting for Authentication endpoints
        RateLimiter::for('auth', function (Request $request) {
            $limit = config('api.rate_limits.auth', 5);

            return Limit::perMinute($limit)->by($request->ip());
        });
    }
}
