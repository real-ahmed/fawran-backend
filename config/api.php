<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Rate Limits
    |--------------------------------------------------------------------------
    |
    | Here you may specify the rate limits for different parts of the API.
    | These limits are used by the RateLimiter in the AppServiceProvider
    | to protect your application from brute-force and DDoS attacks.
    |
    */

    'rate_limits' => [
        'api' => env('RATE_LIMIT_API', 100),
        'auth' => env('RATE_LIMIT_AUTH', 5),
    ],

];
