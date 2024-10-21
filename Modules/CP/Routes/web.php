<?php

use App\Models\Ware;
use Modules\CP\Http\Controllers\web\LevelController;
use Modules\CP\Http\Controllers\web\WeeklyCpController;
use Modules\CP\Http\Controllers\web\LevelGiftController;
use Modules\CP\Http\Controllers\web\CpRelationController;
use Modules\CP\Http\Controllers\web\WeeklyCpGiftController;

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
    function (\Illuminate\Routing\Router $router) {
        $router->resource('cp-relations', CpRelationController::class);
        $router->resource ('weekly-cp',WeeklyCpController::class);
        // $router->resource('cp-levels', LevelController::class);
       

        // Route::prefix('cp-level-gifts/{cp_level_id}/')->group(function () {
        // $router->resource('cp-levels/{id}', LevelController::class);

        Route::prefix('cp-levels/{relation_id}')->group(function () {
            Route::get('/', [LevelController::class, 'index'])->name('cp-levels.index');
            Route::get('/create', [LevelController::class, 'create'])->name('cp-levels.create');
            Route::post('/', [LevelController::class, 'store'])->name('cp-levels.store');
            Route::get('/{id}', [LevelController::class, 'show'])->name('cp-levels.show');
            Route::get('/{id}/edit', [LevelController::class, 'edit'])->name('cp-levels.edit');
            Route::put('/{id}', [LevelController::class, 'update'])->name('cp-levels.update');
            Route::delete('/{id}', [LevelController::class, 'destroy'])->name('cp-levels.destroy');
        });
        Route::prefix('cp-level-gifts/{cp_level_id}')->group(function () {
            Route::get('/', [LevelGiftController::class, 'index']);
            Route::get('/create', [LevelGiftController::class, 'create']);
            Route::post('/', [LevelGiftController::class, 'store']);
            Route::get('/{id}', [LevelGiftController::class, 'show'])->where('id', '[0-9]+');
            Route::get('/{id}/edit', [LevelGiftController::class, 'edit'])->where('id', '[0-9]+');
            Route::put('/{id}', [LevelGiftController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [LevelGiftController::class, 'destroy'])->where('id', '[0-9]+');
        });
        Route::prefix('weekly-cp-gift/{weekly_cp_id}')->group(function () {
            Route::get('/', [WeeklyCpGiftController::class, 'index']);
            Route::get('/{level}/create', [WeeklyCpGiftController::class, 'create']);
            Route::post('/{level}', [WeeklyCpGiftController::class, 'store']);
            Route::get('/{id}', [WeeklyCpGiftController::class, 'show'])->where('id', '[0-9]+');
            Route::get('/{id}/edit', [WeeklyCpGiftController::class, 'edit'])->where('id', '[0-9]+');
            Route::put('/{id}', [WeeklyCpGiftController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [WeeklyCpGiftController::class, 'destroy'])->where('id', '[0-9]+');
        });
    });



