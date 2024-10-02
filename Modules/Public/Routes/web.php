<?php

use Modules\Public\Http\Controllers\web\LevelIntervalController;
use Modules\Public\Http\Controllers\web\RewardLevelIntervalController;



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
    function (\Illuminate\Routing\Router $router) {
        $router->resource('level-intervals', LevelIntervalController::class);

        Route::prefix('reward_level_interval/{level_interval_id}')->group(function () {
            Route::get('/', [RewardLevelIntervalController::class, 'index']);
            Route::get('/create', [RewardLevelIntervalController::class, 'create']);
            Route::post('/', [RewardLevelIntervalController::class, 'store']);
            Route::get('/{id}', [RewardLevelIntervalController::class, 'show'])->where('id', '[0-9]+');
            Route::get('/{id}/edit', [RewardLevelIntervalController::class, 'edit'])->where('id', '[0-9]+');
            Route::put('/{id}', [RewardLevelIntervalController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [RewardLevelIntervalController::class, 'destroy'])->where('id', '[0-9]+');
        });
    });
