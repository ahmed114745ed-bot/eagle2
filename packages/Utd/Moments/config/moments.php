<?php

return [
    /*
    |--------------------------------------------------------------------------
    | External Models Configuration
    |--------------------------------------------------------------------------
    |
    | Configure external model dependencies to make the package independent
    |
    */
    'models' => [
        'gift' => env('MOMENTS_GIFT_MODEL', 'Utd\Gifts\Entities\Gift'),
        
        // User Model
        'user' => env('MOMENTS_USER_MODEL', 'App\Models\User'),
    ],
];
