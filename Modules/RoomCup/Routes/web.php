<?php

use Modules\RoomCup\Http\Controllers\web\RoomCupTargetController;
use Modules\RoomCup\Http\Controllers\web\RoomCupSettingsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(
    [
        'prefix'     => config('admin.route.prefix'),
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            'multiLanguage',
        ],
        'as'         => config('admin.route.prefix') . 'routes',
    ],
    function () {
        Route::resource('room-cup-target', RoomCupTargetController::class);
        Route::resource('room-cup-settings', RoomCupSettingsController::class);
        Route::resource('room-cup-settings', RoomCupSettingsController::class);
 
        
   
    }
);
