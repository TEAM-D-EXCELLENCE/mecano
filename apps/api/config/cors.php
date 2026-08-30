<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'up'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_unique(array_filter(
        array_merge(
            [
                // Admin Frontends
                'https://admin-nine-smoky-13.vercel.app',
                'https://admin.garage.excellenceteam.site',
                // Client Web Frontends
                'https://web-mu-three-85.vercel.app',
                'https://webgarage.excellenceteam.site',
            ],
            // Additional custom origins from environment
            array_filter(explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))),
            [
                env('APP_FRONTEND_URL'),
                env('APP_ADMIN_URL'),
            ]
        )
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => true,

];
