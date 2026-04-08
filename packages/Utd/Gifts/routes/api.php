<?php

use Illuminate\Support\Facades\Route;
use Utd\Gifts\Http\Controllers\Api\GiftController;
use Utd\Gifts\Http\Controllers\Api\GiftCategoryController;
use Utd\Gifts\Http\Controllers\Api\GiftLogController;

Route::prefix('api/')->middleware(['api', 'auth:sanctum'])->group(function () {

    // Gift Routes
    Route::prefix('gifts')->group(function () {
        Route::get('/', [GiftController::class, 'index']);
        Route::get('/v2', [GiftController::class, 'getByCategory']);
        Route::get('/images', [GiftController::class, 'get_images']);
        Route::post('/send', [GiftLogController::class, 'gift_queue_cp']);
        Route::post('/send2', [GiftLogController::class, 'gift_queue_cp']);
        Route::post('/send-lucky-gift-combo', [GiftLogController::class, 'sendLuckyGift2'])
            ->middleware(['checkCpu']);
        Route::post('/v2/send-lucky-gift-combo', [GiftLogController::class, 'sendLuckyGift2V2'])
            ->middleware(['checkCpu']);
    });

    // Gift Categories Routes
    Route::prefix('gift-categories')->group(function () {
        Route::get('/', [GiftCategoryController::class, 'index']);
    });

    // Gift Logs Routes
    Route::prefix('gift-logs')->group(function () {
        Route::get('/', [GiftLogController::class, 'index']);
        Route::get('/report', [GiftLogController::class, 'report']);
    });
});
