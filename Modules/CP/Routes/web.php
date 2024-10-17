<?php

use App\Models\Ware;
use Modules\CP\Http\Controllers\web\CpRelationController;
use Modules\CP\Http\Controllers\web\LevelController;
use Modules\CP\Http\Controllers\web\LevelGiftController;

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
        $router->resource('cp-levels', LevelController::class);

        Route::prefix('cp-level-gifts/{cp_level_id}')->group(function () {
            Route::get('/', [LevelGiftController::class, 'index']);
            Route::get('/create', [LevelGiftController::class, 'create']);
            Route::post('/', [LevelGiftController::class, 'store']);
            Route::get('/{id}', [LevelGiftController::class, 'show'])->where('id', '[0-9]+');
            Route::get('/{id}/edit', [LevelGiftController::class, 'edit'])->where('id', '[0-9]+');
            Route::put('/{id}', [LevelGiftController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [LevelGiftController::class, 'destroy'])->where('id', '[0-9]+');
        });

    });



