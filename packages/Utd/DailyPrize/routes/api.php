<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Utd\DailyPrize\Http\Controllers\Api\DailyGiftController;

Route::middleware(['auth:sanctum', 'localization' ,'update.last.seen'])->group(function () {
    Route::get('current-day',[DailyGiftController::class,'current_day']);
    Route::post('receive-daily-prize',[DailyGiftController::class,'receive_daily_prize']);
});
