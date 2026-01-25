<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reals Feature Configuration
    |--------------------------------------------------------------------------
    */

    // تفعيل/تعطيل الميزة
    'enabled' => env('REALS_ENABLED', true),

    // إعدادات التخزين
    'storage' => [
        'disk' => env('REALS_STORAGE_DISK', 'public'),
        'path' => 'reals',
        'thumbnails_path' => 'reals/thumbnails',
    ],

    // إعدادات الفيديو
    'video' => [
        'max_duration' => env('REALS_MAX_DURATION', 60), // بالثواني
        'max_size' => env('REALS_MAX_SIZE', 50 * 1024 * 1024), // 50MB
        'allowed_extensions' => ['mp4', 'mov', 'avi', 'webm'],
        'thumbnail_width' => 300,
        'thumbnail_height' => 400,
    ],

    // إعدادات الصفحات
    'pagination' => [
        'feed' => 10,
        'user_reals' => 10,
        'comments' => 20,
        'likes' => 20,
    ],

    // إعدادات الـ Cache
    'cache' => [
        'enabled' => env('REALS_CACHE_ENABLED', true),
        'ttl' => 3600, // 1 hour
        'prefix' => 'reals_',
    ],

    // إعدادات التقارير
    'reports' => [
        'auto_hide_threshold' => 5, // عدد التقارير لإخفاء الريل تلقائياً
    ],
];
