<?php

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

use Illuminate\Support\Facades\Route;
use Modules\Badge\Http\Controllers\web\BadgeController;
use Modules\Badge\Http\Controllers\web\UserBadgeController;
use Modules\Badge\Http\Controllers\web\DedicateBadgeController;



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
            'appFeatureEnable:achievement',
        ],
        'as'         => config('admin.route.prefix') . '.',
    ],
    function () {
       // Route::resource('badges', BadgeController::class);

        Route::prefix('badges')->group(function () {
            Route::get('/', [BadgeController::class, 'index']);
            Route::get('/create', [BadgeController::class, 'create']);
            Route::post('/', [BadgeController::class, 'store']);
            Route::get('/{id}', [BadgeController::class, 'show'])->where('id', '[0-9]+');
            Route::get('/{id}/edit', [BadgeController::class, 'edit'])->where('id', '[0-9]+');
            Route::put('/{id}', [BadgeController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [BadgeController::class, 'destroy'])->where('id', '[0-9]+');
        });
        Route::resource('dedicate-badges', DedicateBadgeController::class);
        Route::get('user-badges', [UserBadgeController::class, 'index']);

    }
);
