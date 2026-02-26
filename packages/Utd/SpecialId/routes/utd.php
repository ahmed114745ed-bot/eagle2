<?php

use Illuminate\Support\Facades\Route;
use Utd\SpecialId\Http\Controllers\Utd\SpecialHistoryController;
use Utd\SpecialId\Http\Controllers\Utd\SpecialIdFramController;
use Utd\SpecialId\Http\Controllers\Utd\SpecialIdRequestController;
use Utd\SpecialId\Http\Controllers\Utd\SpecialWareController;

Route::prefix('special-id-fram')->group(function () {
    Route::get('/', [SpecialIdFramController::class, 'index']);
    Route::get('/{id}', [SpecialIdFramController::class, 'show']);
    Route::post('/create', [SpecialIdFramController::class, 'store']);
    Route::post('/update/{id}', [SpecialIdFramController::class, 'update']);
    Route::post('/delete/{id}', [SpecialIdFramController::class, 'delete']);
});

Route::prefix('special-id-requests')->group(function () {
    Route::get('/', [SpecialIdRequestController::class, 'index']);
    Route::get('/{id}', [SpecialIdRequestController::class, 'show']);
    Route::post('/update/{id}', [SpecialIdRequestController::class, 'update']);
    Route::post('/delete/{id}', [SpecialIdRequestController::class, 'delete']);
    Route::post('/delete-all', [SpecialIdRequestController::class, 'delete_all']);
});

Route::prefix('special-wares')->group(function () {
    Route::get('/', [SpecialWareController::class, 'index']);
    Route::get('/{id}', [SpecialWareController::class, 'show']);
    Route::post('/create', [SpecialWareController::class, 'store']);
    Route::post('/update/{id}', [SpecialWareController::class, 'update']);
    Route::post('/update-enable/{id}', [SpecialWareController::class, 'update_enable']);
    Route::post('/delete/{id}', [SpecialWareController::class, 'delete']);
    Route::post('/delete-all', [SpecialWareController::class, 'delete_all']);
});

Route::prefix('special-histories')->group(function () {
    Route::get('/', [SpecialHistoryController::class, 'index']);
    Route::post('/delete-all', [SpecialHistoryController::class, 'delete_all']);
    Route::post('/delete/{id}', [SpecialHistoryController::class, 'delete']);
});
