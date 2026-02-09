<?php

use Illuminate\Support\Facades\Route;
use Utd\Charizma\Http\Controllers\CharizmaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'appFeatureEnable:charizma', 'update.last.seen'])->group(function () {
    Route::prefix('charisma')->group(function () {
        Route::post('/change-status', [CharizmaController::class, 'changeStatus']);
        Route::post('/reset', [CharizmaController::class, 'reset']);
    });
});
