<?php

use Illuminate\Support\Facades\Route;
use Utd\Vip\Http\Controllers\Web\WareVipController;

Route::group(
    [
        'prefix' => config('admin.route.prefix'),
        'middleware' => [
            'web',
            'admin',
        ],
        'as' => config('admin.route.prefix').'.',
    ],
    function () {
        Route::resource('wares-vips', WareVipController::class);
    }
);
