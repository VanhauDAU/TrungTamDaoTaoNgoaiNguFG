<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\Auth\TaiKhoan::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the number of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

    /*
    |--------------------------------------------------------------------------
    | Auth Rate Limiters
    |--------------------------------------------------------------------------
    |
    | These limits are applied before controller logic is executed. The goal
    | is to block abusive traffic early while keeping the domain-specific
    | login lockout flow intact for real credential failures.
    |
    */

    'rate_limiters' => [
        'login' => [
            'per_minute' => env('AUTH_LOGIN_RATE_LIMIT_PER_MINUTE', 12),
            'per_ip_per_minute' => env('AUTH_LOGIN_RATE_LIMIT_IP_PER_MINUTE', 30),
        ],
        'register' => [
            'per_minute' => env('AUTH_REGISTER_RATE_LIMIT_PER_MINUTE', 6),
            'per_ip_per_minute' => env('AUTH_REGISTER_RATE_LIMIT_IP_PER_MINUTE', 12),
        ],
        'email_check' => [
            'per_minute' => env('AUTH_EMAIL_CHECK_RATE_LIMIT_PER_MINUTE', 30),
            'per_ip_per_minute' => env('AUTH_EMAIL_CHECK_RATE_LIMIT_IP_PER_MINUTE', 120),
        ],
    ],

    'register_email_check_cache_store' => env(
        'REGISTER_EMAIL_CHECK_CACHE_STORE',
        env('APP_ENV') === 'testing' ? 'array' : 'redis_fallback'
    ),

    'register_email_check_cache_ttl' => (int) env('REGISTER_EMAIL_CHECK_CACHE_TTL', 60),

];
