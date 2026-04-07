<?php

$gcsCommon = [
    'driver'         => 'gcs',
    'key_file_path'  => env('GOOGLE_CLOUD_KEY_FILE', base_path('docker/gcp.json')),
    'project_id'     => env('GOOGLE_CLOUD_PROJECT_ID', 'rixo-chat'),
    'bucket'         => env('GOOGLE_CLOUD_STORAGE_BUCKET', 'rixo-chat'),
    'visibility'     => 'public',
    'metadata'       => ['cacheControl' => 'public,max-age=86400'],
];

$bucketName = $gcsCommon['bucket'];

return [

    'default' => env('FILESYSTEM_DISK', 'gcs'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app'),
        ],

        'public' => array_merge($gcsCommon, [
            'path_prefix' => 'public',
            'url'         => "https://storage.googleapis.com/$bucketName/public",
        ]),

        'images' => array_merge($gcsCommon, [
            'path_prefix' => 'images',
            'url'         => "https://storage.googleapis.com/$bucketName/images",
        ]),

        'profile' => array_merge($gcsCommon, [
            'path_prefix' => 'profile',
            'url'         => "https://storage.googleapis.com/$bucketName/profile",
        ]),

        'conversation' => array_merge($gcsCommon, [
            'path_prefix' => 'conversation',
            'url'         => "https://storage.googleapis.com/$bucketName/conversation",
        ]),

        'rooms' => array_merge($gcsCommon, [
            'path_prefix' => 'rooms',
            'url'         => "https://storage.googleapis.com/$bucketName/rooms",
        ]),

        'families' => array_merge($gcsCommon, [
            'path_prefix' => 'families',
            'url'         => "https://storage.googleapis.com/$bucketName/families",
        ]),

        'unions' => array_merge($gcsCommon, [
            'path_prefix' => 'unions',
            'url'         => "https://storage.googleapis.com/$bucketName/unions",
        ]),

        'ticket' => array_merge($gcsCommon, [
            'path_prefix' => 'ticket',
            'url'         => "https://storage.googleapis.com/$bucketName/ticket",
        ]),

        'videos' => array_merge($gcsCommon, [
            'path_prefix' => 'videos',
            'url'         => "https://storage.googleapis.com/$bucketName/videos",
        ]),

        'admin' => array_merge($gcsCommon, [
            'path_prefix' => 'admin',
            'url'         => "https://storage.googleapis.com/$bucketName/admin",
        ]),

        'custom' => array_merge($gcsCommon, [
            'path_prefix' => 'custom',
            'url'         => "https://storage.googleapis.com/$bucketName/custom",
        ]),

        'gcs' => array_merge($gcsCommon, [
            'path_prefix' => env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', ''),
            'url'         => "https://storage.googleapis.com/$bucketName",
        ]),

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
