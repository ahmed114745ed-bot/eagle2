<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('dynamictheme::welcome');
// });

// Client Dashboard route (Screen Builder with Configuration system)
Route::get('/dashboard', function () {
    return view('dynamictheme::dashboard');
});

// Admin Panel route
Route::get('/admin2', function () {
    return view('dynamictheme::admin');
});
