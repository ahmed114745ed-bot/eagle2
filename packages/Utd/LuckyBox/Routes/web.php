<?php

use Illuminate\Support\Facades\Route;
use Utd\LuckyBox\Http\Controllers\Web\BoxUseController;
use Utd\LuckyBox\Http\Controllers\Web\LuckyBoxController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the LuckyBoxServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(
    [
        'prefix' => config('admin.route.prefix'),

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
        Route::resource('lucky-boxes', LuckyBoxController::class);
        Route::get('lucky-box-settings', [LuckyBoxController::class, 'box_settings']);
        Route::resource('thrown-boxes', BoxUseController::class);
    }
);
