<?php

use Illuminate\Support\Facades\Route;
use Utd\UsersWallet\Http\Controllers\Api\ExchangeController as ExchangeDiamondController;
use Utd\UsersWallet\Http\Controllers\Web\ExchangeController;

Route::prefix('exchanges')->group(function () {
    Route::get('/', [ExchangeController::class, 'all']);
    Route::get('/show/{id}', [ExchangeController::class, 'show']);
    Route::post('/create', [ExchangeController::class, 'create']);
    Route::post('/update/{id}', [ExchangeController::class, 'update']);
    Route::delete('/delete/{id}', [ExchangeController::class, 'destroy']);
});

Route::prefix('users')->group(function () {
    Route::get('exchange-diamonds/{id}', [ExchangeDiamondController::class, 'UserExchangeLogs']);
});
