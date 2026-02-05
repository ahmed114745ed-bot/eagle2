<?php

use Illuminate\Support\Facades\Route;
use Utd\Pk\Http\Controllers\Api\PkController;

/*
|--------------------------------------------------------------------------
| PK API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the PkServiceProvider within a group which
| contains the "api" middleware group.
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // PK routes with Zego
    Route::group([],function () {
        Route::post('create-pk', [PkController::class, 'createPK']);
        Route::post('close-pk', [PkController::class, 'closePK']);
        Route::post('show-pk', [PkController::class, 'showPK']);
        Route::post('hide-pk', [PkController::class, 'hidePk']);
    });

    // PK routes without Zego
    Route::prefix('pk')->group(function () {
        Route::post('create', [PkController::class, 'createPKWithoutZego']);
        Route::post('close', [PkController::class, 'closePKWithoutZego']);
        Route::post('show', [PkController::class, 'showPKWithoutZego']);
        Route::post('hide', [PkController::class, 'hidePkWithoutZego']);
    });

    // Room PK history
    Route::get('room/{id}/pks', [PkController::class, 'roomPk']);
});
