<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Room Web Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RoomServiceProvider within a group which
| contains the "web" middleware group.
|
*/

Route::group(
    [
        'prefix'     => config('admin.route.prefix'),
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
            'appFeatureEnable:achievement',
        ],
        'as'         => config('admin.route.prefix') . '.',
    ],
    function () {
    }
);
