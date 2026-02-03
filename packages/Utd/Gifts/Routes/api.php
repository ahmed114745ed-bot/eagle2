<?php

use Illuminate\Support\Facades\Route;
use Utd\Gifts\Http\Controllers\GiftsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api/gifts')->middleware(['api'])->group(function () {
    Route::get('/', [GiftsController::class, 'index']);
    Route::post('/', [GiftsController::class, 'store']);
    Route::get('/{id}', [GiftsController::class, 'show']);
    Route::put('/{id}', [GiftsController::class, 'update']);
    Route::delete('/{id}', [GiftsController::class, 'destroy']);
});
