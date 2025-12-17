<?php

use Modules\Reals\Http\Controllers\web\ReelController;
use Modules\Reals\Http\Controllers\web\ReportRealsController;
use Modules\Reals\Http\Controllers\web\ReelSettingsController;
use Modules\Public\Http\Controllers\web\UpgradeLevelController;

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

Route::prefix('reals')->middleware("appFeatureEnable:reel")->group(function() {
    Route::get('/', 'RealsController@index');
    Route::get('delete-reel/{real_id}/{id}', 'RealsController@destroy_dash')->name('delete-reel')->middleware(['appFeatureEnable:reel']);
});

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



        Route::resource('report-reals', ReportRealsController::class);
         Route::resource('reels', ReelController::class);
        Route::resource('reel-settings', ReelSettingsController::class);
         Route::post('reel-config', [UpgradeLevelController::class, 'reelConfig'])->name('reel-config');

    }
);
