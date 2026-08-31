<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Media Upload & Storage Configurations
    |--------------------------------------------------------------------------
    |
    | Limits, MIME types, and providers for photos and videos according to
    | project architecture guidelines (M1).
    |
    */

    'driver' => env('MEDIA_IMAGE_DRIVER', 'cloudinary'),

    /** Numéro de repli si aucun n'est enregistré dans les réglages du garage. */
    'default_whatsapp_number' => env('DEFAULT_WHATSAPP_NUMBER', '+237699001122'),

    'photos' => [
        'max_size_bytes' => 15 * 1024 * 1024, // 15 MB
        'allowed_mimes' => [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/heic',
        ],
        'upload_folder' => env('CLOUDINARY_UPLOAD_FOLDER', 'mecano/cars'),
        'signature_ttl_seconds' => 600, // 10 minutes
    ],

    'videos' => [
        'max_size_bytes' => 200 * 1024 * 1024, // 200 MB
        'max_count_per_car' => 2, // 1 interior + 1 exterior
        'allowed_mimes' => [
            'video/mp4',
            'video/quicktime',
        ],
        'signature_ttl_seconds' => 900, // 15 minutes
    ],

    'cloudinary' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME', ''),
        'api_key' => env('CLOUDINARY_API_KEY', ''),
        'api_secret' => env('CLOUDINARY_API_SECRET', ''),
        'secure' => true,
    ],


    /*
    |--------------------------------------------------------------------------
    | Named image transformation presets (Cloudinary)
    |--------------------------------------------------------------------------
    */
    'transform_presets' => [
        'thumb' => 'w_200,h_150,c_fill,f_auto,q_auto',
        'card' => 'w_640,h_480,c_fill,g_auto,f_auto,q_auto',
        'detail' => 'w_1280,h_960,c_limit,f_auto,q_auto',
        'og' => 'w_1200,h_630,c_fill,g_auto,f_auto,q_auto',
    ],
];
