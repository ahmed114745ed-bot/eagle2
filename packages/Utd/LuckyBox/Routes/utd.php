<?php

use Illuminate\Support\Facades\Route;
use Utd\LuckyBox\Http\Controllers\Utd\BoxController;
use Utd\LuckyBox\Http\Controllers\Utd\BoxUseController;

/*
|--------------------------------------------------------------------------
| LuckyBox UTD Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the LuckyBoxServiceProvider within a group for
| the UTD (old admin API) routes.
|
*/

Route::group([
    'middleware' => ['api', 'auth:sanctum'],
    'prefix' => 'api/utd'
], function () {
    Route::prefix('boxes')->group(function () {
        Route::get('/', [BoxController::class, 'index']);
        Route::post('/create', [BoxController::class, 'store']);
        Route::post('/update/{id}', [BoxController::class, 'update']);
        Route::post('/delete/{id}', [BoxController::class, 'delete']);
        Route::post('/delete-all', [BoxController::class, 'delete_all']);
        Route::get('/{id}', [BoxController::class, 'show']);
    });

    Route::prefix('thrown-boxes')->group(function () {
        Route::get('/', [BoxUseController::class, 'index']);
        Route::post('/update/{id}', [BoxUseController::class, 'update']);
        Route::post('/delete/{id}', [BoxUseController::class, 'delete']);
        Route::post('/delete-all', [BoxUseController::class, 'delete_all']);
        Route::get('/{id}', [BoxUseController::class, 'show']);
    });
});
