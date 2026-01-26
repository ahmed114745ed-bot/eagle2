<?php

use Illuminate\Support\Facades\Route;
use Utd\Agency\Http\Controllers\Api\AgencyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'checkLatestToken', 'generalBan', 'appFeatureEnable:agencies', 'update.last.seen'])
    ->prefix('agencies')
    ->group(function () {
        
        // Get active agencies
        Route::get('/', [AgencyController::class, 'activeAgencies']);
        
        // Get agency details
        Route::get('/{id}', [AgencyController::class, 'show']);
        
        // Get agency members
        Route::get('/{id}/members', [AgencyController::class, 'members']);
        
        // Get agency target
        Route::get('/{id}/target', [AgencyController::class, 'target']);
        
        // Get agency stars
        Route::get('/{id}/stars', [AgencyController::class, 'stars']);
        
        // Get agency heroes
        Route::get('/{id}/heroes', [AgencyController::class, 'heroes']);
        
        // Join agency
        Route::post('/join', [AgencyController::class, 'joinRequest']);
        
        // Leave agency
        Route::post('/leave', [AgencyController::class, 'leaveAgency']);
        
        // Get user history
        Route::get('/history', [AgencyController::class, 'history']);
    });
