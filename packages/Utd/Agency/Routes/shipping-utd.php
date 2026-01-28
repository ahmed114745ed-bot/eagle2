<?php

use Illuminate\Support\Facades\Route;
use Utd\Agency\Http\Controllers\Shipping\Utd\AppearChargerAgencyController;

/*
|--------------------------------------------------------------------------
| Shipping Agency UTD API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'checkLatestToken', 'generalBan', 'userBan', 'update.last.seen'])->group(function () {
    Route::prefix('appear-charger-agency')->group(function () {
        Route::get('/', [AppearChargerAgencyController::class, 'index']);
        Route::post('update/{id}', [AppearChargerAgencyController::class, 'update']);
    });
});
