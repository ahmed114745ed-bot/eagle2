<?php

use Illuminate\Support\Facades\Route;
use Utd\ShippingAgency\Http\Controllers\utd\AppearChargerAgencyController;

/*
|--------------------------------------------------------------------------
| UTD API Routes
|--------------------------------------------------------------------------
|
| Routes for UTD API endpoints.
|
*/

Route::middleware(['auth:sanctum', 'checkLatestToken', 'generalBan', 'userBan', 'update.last.seen'])->group(function () {
    Route::prefix('appear-charger-agency')->group(function () {
        Route::get('/', [AppearChargerAgencyController::class, 'index']);
        Route::post('update/{id}', [AppearChargerAgencyController::class, 'update']);
    });
});
