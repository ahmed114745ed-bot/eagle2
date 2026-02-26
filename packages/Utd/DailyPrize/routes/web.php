<?php

use Illuminate\Support\Facades\Route;
use Utd\DailyPrize\Http\Controllers\web\DailyPrizeController;
use Utd\DailyPrize\Http\Controllers\web\DailyPrizeTypeController;

Route::group(
    [
        'prefix' => config('admin.route.prefix'),
        'namespace' => 'web',
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            'multiLanguage',
        ],
        'as' => config('admin.route.prefix').'.',
    ],
    function () {
        Route::resource('daily-gift-types', DailyPrizeTypeController::class);
        Route::prefix('daily-gifts/{type}')->group(function () {
            Route::get('/', [DailyPrizeController::class, 'index'])->name('daily-gifts.index');
            Route::get('/create', [DailyPrizeController::class, 'create']);
            Route::post('/', [DailyPrizeController::class, 'store']);
            Route::get('/{id}', [DailyPrizeController::class, 'show'])->where('id', '[0-9]+');
            Route::get('/{id}/edit', [DailyPrizeController::class, 'edit'])->where('id', '[0-9]+');
            Route::put('/{id}', [DailyPrizeController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [DailyPrizeController::class, 'destroy'])->where('id', '[0-9]+');
        });
    }
);
