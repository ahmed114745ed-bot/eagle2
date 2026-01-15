<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('dynamictheme::welcome');
// });

// Client Dashboard route (Screen Builder with Configuration system)

 Route::group(
    [
        'prefix'     => config('admin.route.prefix'),
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            'multiLanguage',
        ],
        'as' => config('admin.route.prefix'), 
    ],
    function () {
        Route::get('/theme-dashboard', function () {
            return view('dynamictheme::dashboard');
        });

        // Admin Panel route
        Route::get('/theme-admin', function () {
            return view('dynamictheme::admin');
        });

        // Child Customizer route
        Route::get('/child-customizers', function () {
            return view('dynamictheme::child-customizer');
        });
});
