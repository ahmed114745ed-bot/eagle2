<?php

use Illuminate\Support\Facades\Route;
use Utd\DailyPrize\Http\Controllers\utd\DailyGiftsController;
use Utd\DailyPrize\Http\Controllers\utd\DailyGiftTypesController;

Route::prefix('daily-gift-types')->group(function () {
    Route::get('/', [DailyGiftTypesController::class, 'index']);
    Route::get('/{id}', [DailyGiftTypesController::class, 'show']);
    Route::post('/create', [DailyGiftTypesController::class, 'store']);
    Route::post('/update/{id}', [DailyGiftTypesController::class, 'update']);
    Route::post('/delete/{id}', [DailyGiftTypesController::class, 'delete']);
});

Route::prefix('daily-gifts/{type}')->group(function () {
    Route::get('/', [DailyGiftsController::class, 'index']);
    Route::get('/{id}', [DailyGiftsController::class, 'show']);
    Route::post('/create', [DailyGiftsController::class, 'store']);
    Route::post('/update/{id}', [DailyGiftsController::class, 'update']);
    Route::post('/delete/{id}', [DailyGiftsController::class, 'delete']);
});
