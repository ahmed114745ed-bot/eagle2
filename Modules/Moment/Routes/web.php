<?php

use Modules\Moment\Http\Controllers\web\MomentController;
use Modules\Moment\Http\Controllers\web\ReportMomentController;
use Modules\Public\Http\Controllers\web\UpgradeLevelController;
use Modules\Moment\Http\Controllers\web\MomentSettingsController;

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

Route::prefix('moment')->middleware(['appFeatureEnable:moment'])->group(function () {
    Route::get('/', 'MomentController@index');
});

Route::get('delete-moment/{moment_id}/{id}', 'MomentController@destroy_dash')->name('delete-moment')->middleware(['appFeatureEnable:moment']);

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


        Route::resource('report-moments', ReportMomentController::class)->middleware('moment.allowed');
        Route::post('moment-config', [UpgradeLevelController::class, 'momentConfig'])->name('moment-config');
        Route::resource('moments', MomentController::class)->middleware('moment.allowed');
        Route::get('moment-gallery/{id}', [MomentController::class, 'momentGallery'])->middleware('moment.allowed');
        Route::resource('moment-settings', MomentSettingsController::class)->middleware('moment.allowed');
    }
);
