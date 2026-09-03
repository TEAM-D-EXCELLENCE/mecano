<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'frontend' => [
        'revalidate_url' => env('FRONTEND_REVALIDATE_URL', 'http://localhost:3000/api/revalidate'),
        'revalidate_secret' => env('REVALIDATE_SECRET', 'test-revalidate-secret'),
    ],

    /*
    |--------------------------------------------------------------------------
    | remove.bg
    |--------------------------------------------------------------------------
    |
    | Plafond mensuel lu par IntegrationQuota. Le plan gratuit est limité à
    | cinquante détourages ; le dépasser facture sans prévenir, d'où le
    | comptage transactionnel côté application (CDC §3.2, écart E6).
    |
    */

    'removebg' => [
        'monthly_limit' => (int) env('REMOVE_BG_MONTHLY_QUOTA', 50),
    ],

];
