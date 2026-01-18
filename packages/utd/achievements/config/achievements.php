<?php

return [
    /*
    |--------------------------------------------------------------------------
    | License Configuration
    |--------------------------------------------------------------------------
    */
    'license_key' => env('ACHIEVEMENTS_LICENSE_KEY'),
    'secret' => env('ACHIEVEMENTS_SECRET', 'utd-achievements-2024'),

    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which models the package should use.
    | This allows COMPLETE DECOUPLING from the base project.
    |
    */
    'models' => [
        // The User model class - must have 'id' attribute
        'user' => env('ACHIEVEMENTS_USER_MODEL', 'App\\Models\\User'),

        // The Gift model class (optional) - for gift achievements
        'gift' => env('ACHIEVEMENTS_GIFT_MODEL', 'App\\Models\\Gift'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Customize table names if needed
    |
    */
    'tables' => [
        'achievements' => 'achievements',
        'achievement_levels' => 'achievement_levels',
        'user_achievements' => 'user_achievements',
        'user_achievement_levels' => 'user_achievement_levels',
        'gift_achievements' => 'gift_achievements',
    ],

    /*
    |--------------------------------------------------------------------------
    | Foreign Keys
    |--------------------------------------------------------------------------
    |
    | Configure foreign key column names
    |
    */
    'foreign_keys' => [
        'user' => 'user_id',
        'gift' => 'gift_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */
    'features' => [
        'charging_achievements' => true,
        'room_achievements' => true,
        'gift_achievements' => true,
        'monthly_reset' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes Configuration
    |--------------------------------------------------------------------------
    */
    'routes' => [
        // API Routes
        'api_prefix' => 'api/achievements',
        'api_middleware' => ['api', 'auth:sanctum'],
        'api_enabled' => true,

        // Web/Admin Routes
        'web_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Configuration (Laravel Admin / Encore)
    |--------------------------------------------------------------------------
    |
    | If Encore Laravel-Admin is installed, the package provides
    | ready-made admin controllers and routes.
    |
    */
    'admin' => [
        // Permission name prefix for admin actions
        'permission' => 'achievement',

        // Menu configuration
        'menu' => [
            'title' => 'Achievements',
            'icon' => 'fa-trophy',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'disk' => 'public',
        'folder' => 'achievements',
    ],
];
