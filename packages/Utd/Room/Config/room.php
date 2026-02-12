<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Room Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the Room package
    |
    */

    // Default room settings
    'default_microphones' => 8,
    'max_visitors' => 500,

    // Room types
    'types' => [
        'audio',
        'live',
        'video',
    ],

    // Room modes
    'modes' => [
        'normal',
        'cinema',
        'pk',
    ],

    // Background settings
    'backgrounds' => [
        'allow_custom' => true,
        'max_size' => 5120, // KB
    ],

    // Salary settings
    'salary' => [
        'enabled' => true,
    ],

    // PK settings
    'pk' => [
        'enabled' => true,
        'duration' => 300, // seconds
    ],
];
