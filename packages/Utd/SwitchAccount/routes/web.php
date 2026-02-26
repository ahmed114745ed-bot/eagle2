<?php

use Illuminate\Support\Facades\Route;
use Utd\SwitchAccount\Http\Controllers\web\UsersDevicesHistoriesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::group(
    [
        'prefix' => config('admin.route.prefix'),
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            'multiLanguage',
        ],
        'as' => config('admin.route.prefix').'.',
    ],
    function () {
        Route::resource('user-devices-histories', UsersDevicesHistoriesController::class);
    }
);
