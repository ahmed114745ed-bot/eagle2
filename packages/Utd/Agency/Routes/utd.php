<?php

use Illuminate\Support\Facades\Route;
use Utd\Agency\Http\Controllers\Utd\AgencyController;

/*
|--------------------------------------------------------------------------
| UTD API Routes
|--------------------------------------------------------------------------
*/
Route::group([
    'middleware' => ['api', 'localization'],
    'prefix' => 'api/utd',
], function () {
    Route::prefix('agencies')->group(function () {

        // Get all agency requests
        Route::get('/', [AgencyController::class, 'index']);

        // Action on request
        Route::post('/action', [AgencyController::class, 'actionRequestAgency']);

        // Active agencies
        Route::get('/active', [AgencyController::class, 'activeAgencies']);

        // All agencies
        Route::get('/all', [AgencyController::class, 'allAgencies']);

        // Active agencies members
        Route::get('/members', [AgencyController::class, 'activeAgenciesMembers']);

        // Create agency
        Route::post('/create', [AgencyController::class, 'create']);

        // Update agency
        Route::post('/update/{id}', [AgencyController::class, 'update']);

        // Delete agency
        Route::delete('/delete/{id}', [AgencyController::class, 'destroy']);

        // Report
        Route::get('/report', [AgencyController::class, 'report']);
    });
});
