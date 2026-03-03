<?php

use Illuminate\Support\Facades\Route;
use Utd\Badge\Http\Controllers\web\BadgeController;
use Utd\Badge\Http\Controllers\web\UserBadgeController;
use Utd\Badge\Http\Controllers\web\DedicateBadgeController;

Route::group(
    [
        'prefix'     => config('admin.route.prefix'),
        'namespace'  => 'web',
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            'multiLanguage',
            'appFeatureEnable:achievement',
        ],
        'as'         => config('admin.route.prefix') . '.',
    ],
    function () {
        Route::resource('badges', BadgeController::class);
        Route::resource('dedicate-badges', DedicateBadgeController::class);
        Route::get('user-badges', [UserBadgeController::class, 'index']);
    }
);
