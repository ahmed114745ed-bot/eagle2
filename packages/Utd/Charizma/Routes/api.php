<?php

use Illuminate\Support\Facades\Route;
use Utd\Charizma\Http\Controllers\CharizmaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['auth:sanctum', 'update.last.seen', 'userBan'], 'prefix' => 'api'], function () {
    Route::prefix('charisma')->group(function () {
        Route::post('/change-status', [CharizmaController::class, 'changeStatus']);
        Route::post('/reset', [CharizmaController::class, 'reset']);
    });
});
