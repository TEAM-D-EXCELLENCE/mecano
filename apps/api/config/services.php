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

    /*
    | Webhook de revalidation de la vitrine.
    |
    | Les deux clés portent le nom que l'environnement fournit réellement. Elles
    | en portaient un autre : l'URL retombait sur `localhost` — le conteneur se
    | parlait à lui-même — et la signature était calculée avec un secret par
    | défaut inscrit dans le dépôt, donc connu de tous.
    |
    | Plus de valeur de repli pour le secret : mieux vaut un échec explicite
    | qu'une signature que la vitrine rejettera sans que personne ne le voie.
    */
    'frontend' => [
        'revalidate_url' => env('FRONTEND_REVALIDATE_URL'),
        'revalidate_secret' => env('REVALIDATION_SECRET'),
    ],

];
