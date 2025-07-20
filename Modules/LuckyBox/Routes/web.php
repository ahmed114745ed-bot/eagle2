<?php

use Modules\LuckyBox\Http\Controllers\Web\LuckyBoxController;



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
        'namespace'  => 'web',
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
        ],
        'as'         => config('admin.route.prefix') . '.',
    ],
    function () {
         Route::resource('boxes', 'LuckyBoxController');
        Route::get('lucy-box-settings', [LuckyBoxController::class, 'box_settings']);
        Route::resource('thrown_boxes', 'BoxUseController');
    }
);
