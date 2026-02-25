<?php

use Illuminate\Support\Facades\Route;
use Utd\Pk\Http\Controllers\Utd\PkEventController;

/*
|--------------------------------------------------------------------------
| PK UTD Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the PkServiceProvider within a group for
| the UTD (old admin API) routes.
|
*/

Route::group([
    'middleware' => ['api', 'localization'],
    'prefix' => 'api/utd',
], function () {
    Route::prefix('pk-event')->group(function () {
        Route::get('/', [PkEventController::class, 'index']);
        Route::get('/show/{id}', [PkEventController::class, 'show']);
        Route::post('/create', [PkEventController::class, 'store']);
        Route::delete('/delete/{id}', [PkEventController::class, 'destroy']);
        Route::post('/update/{id}', [PkEventController::class, 'update']);
        Route::get('/default-date', [PkEventController::class, 'defaultDate']);
    });

    Route::prefix('pk-event-gift')->group(function () {
        Route::get('/', [PkEventController::class, 'allGifts']);
        Route::get('/show/{id}', [PkEventController::class, 'showGift']);
        Route::post('/create', [PkEventController::class, 'storeGift']);
        Route::delete('/delete/{id}', [PkEventController::class, 'destroyGift']);
        Route::post('/update/{id}', [PkEventController::class, 'updateGift']);
    });
});
