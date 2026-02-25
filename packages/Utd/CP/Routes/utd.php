<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Route;
use Utd\CP\Http\Controllers\Utd\CpRelationController;
use Utd\CP\Http\Controllers\Utd\LevelController;
use Utd\CP\Http\Controllers\Utd\LevelGiftController;

Route::group([
    'middleware' => ['api', 'localization'],
    'prefix' => 'api/utd',
], function () {
    Route::prefix('cp-relations')->group(function () {
        Route::get('/', [CpRelationController::class, 'index']);
        Route::post('/create', [CpRelationController::class, 'store']);
        Route::post('/update/{id}', [CpRelationController::class, 'update']);
        Route::post('/delete/{id}', [CpRelationController::class, 'delete']);
        Route::post('/delete-all', [CpRelationController::class, 'delete_all']);
        Route::get('/{id}', [CpRelationController::class, 'show']);
    });
    Route::get('cp-types', [CpRelationController::class, 'types']);

    Route::prefix('cp-levels/{relation_id}')->group(function () {
        Route::get('/', [LevelController::class, 'index']);
        Route::post('/create', [LevelController::class, 'store']);
        Route::post('/update/{id}', [LevelController::class, 'update']);
        Route::post('/delete/{id}', [LevelController::class, 'delete']);
        Route::post('/delete-all', [LevelController::class, 'delete_all']);
        Route::get('/{id}', [LevelController::class, 'show']);
    });

    Route::prefix('cp-level-gifts/{cp_level_id}')->group(function () {
        Route::get('/', [LevelGiftController::class, 'index']);
        Route::post('/create', [LevelGiftController::class, 'store']);
        Route::get('/show/{id}', [LevelGiftController::class, 'show']);
        Route::post('/update/{id}', [LevelGiftController::class, 'update']);
        Route::post('/delete/{id}', [LevelGiftController::class, 'delete']);
        Route::post('/delete-all', [LevelGiftController::class, 'delete_all']);
    });
});
