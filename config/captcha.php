<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Captcha Driver
    |--------------------------------------------------------------------------
    |
    | The driver used whenever a driver is not explicitly requested, e.g. via
    | <x-captcha driver="..." /> or new \Lacaptcha\Lacaptcha\Rules\Captcha('...').
    | Supported out of the box: "hcaptcha", "recaptcha", "turnstile", "null".
    |
    */

    'default' => env('CAPTCHA_DRIVER', 'hcaptcha'),

    /*
    |--------------------------------------------------------------------------
    | Captcha Drivers
    |--------------------------------------------------------------------------
    |
    | Each driver keeps its own configuration block, keyed by driver name.
    |
    */

    'drivers' => [

        'hcaptcha' => [
            'site_key' => env('HCAPTCHA_SITE_KEY'),
            'secret_key' => env('HCAPTCHA_SECRET_KEY'),
            'options' => [
                'theme' => env('HCAPTCHA_THEME', 'light'),
                'size' => env('HCAPTCHA_SIZE', 'normal'),
            ],
        ],

        'recaptcha' => [
            // 'v2' or 'v3'.
            'version' => env('RECAPTCHA_VERSION', 'v2'),
            'site_key' => env('RECAPTCHA_SITE_KEY'),
            'secret_key' => env('RECAPTCHA_SECRET_KEY'),
            // v3 only: the action name expected to match the token's action.
            'action' => env('RECAPTCHA_ACTION', 'submit'),
            // v3 only: minimum score (0.0-1.0) required to pass verification.
            'min_score' => (float) env('RECAPTCHA_MIN_SCORE', 0.5),
        ],

        'turnstile' => [
            'site_key' => env('TURNSTILE_SITE_KEY'),
            'secret_key' => env('TURNSTILE_SECRET_KEY'),
            'options' => [
                'theme' => env('TURNSTILE_THEME', 'auto'),
            ],
        ],

        'null' => [
            // Always resolves to this value; no HTTP call is made. Handy for
            // local/testing environments (CAPTCHA_DRIVER=null).
            'succeeds' => env('CAPTCHA_NULL_SUCCEEDS', true),

            // The null driver always passes verification, so it is refused
            // outside local/testing unless this is explicitly set to true.
            // Guards against a stray CAPTCHA_DRIVER=null silently disabling
            // captcha protection in production.
            'allow_in_production' => env('CAPTCHA_NULL_ALLOW_IN_PRODUCTION', false),
        ],

    ],

];
