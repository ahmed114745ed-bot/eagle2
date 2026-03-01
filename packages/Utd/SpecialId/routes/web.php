<?php

use Illuminate\Support\Facades\Route;
use Utd\SpecialId\Http\Controllers\web\SpecialHistoryController;
use Utd\SpecialId\Http\Controllers\web\SpecialIdFramController;
use Utd\SpecialId\Http\Controllers\web\SpecialIdRequestController;
use Utd\SpecialId\Http\Controllers\web\SpecialWareController;

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
        'prefix' => config('admin.route.prefix'),
        'namespace' => 'web',
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
        ],
        'as' => config('admin.route.prefix').'.',
    ],
    function () {
        Route::resource('special-wares', SpecialWareController::class);
        Route::resource('special-histories', SpecialHistoryController::class);
        Route::resource('special-id-fram', SpecialIdFramController::class);
        Route::resource('special-id-requests', SpecialIdRequestController::class);
    }
);
