<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reals Feature Configuration
    |--------------------------------------------------------------------------
    */

    'enabled' => env('REALS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | License Key
    |--------------------------------------------------------------------------
    */
    'license_key' => env('REALS_LICENSE_KEY', ''),

    'storage' => [
        'disk' => env('REALS_STORAGE_DISK', 'public'),
        'path' => 'reals',
        'thumbnails_path' => 'reals/thumbnails',
    ],

    'video' => [
        'max_duration' => env('REALS_MAX_DURATION', 60), 
        'max_size' => env('REALS_MAX_SIZE', 50 * 1024 * 1024), 
        'allowed_extensions' => ['mp4', 'mov', 'avi', 'webm'],
        'thumbnail_width' => 300,
        'thumbnail_height' => 400,
    ],

    'pagination' => [
        'feed' => 10,
        'user_reals' => 10,
        'comments' => 20,
        'likes' => 20,
    ],

    'cache' => [
        'enabled' => env('REALS_CACHE_ENABLED', true),
        'ttl' => 3600, 
        'prefix' => 'reals_',
    ],

    'reports' => [
        'auto_hide_threshold' => 5,
    ],
];
