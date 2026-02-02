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
        
        // Get user history (must be before /{id} route to avoid conflict)
        Route::get('/history', [AgencyController::class, 'history']);
        
        // Get agency details
        Route::get('/{id}', [AgencyController::class, 'show'])->where('id', '[0-9]+');
        
        // Get agency members
        Route::get('/{id}/members', [AgencyController::class, 'members'])->where('id', '[0-9]+');
        
        // Get agency target
        Route::get('/{id}/target', [AgencyController::class, 'target'])->where('id', '[0-9]+');
        
        // Get agency stars
        Route::get('/{id}/stars', [AgencyController::class, 'stars'])->where('id', '[0-9]+');
        
        // Get agency heroes
        Route::get('/{id}/heroes', [AgencyController::class, 'heroes'])->where('id', '[0-9]+');
        
        // Join agency
        Route::post('/join', [AgencyController::class, 'joinRequest']);
        
        // Leave agency
        Route::post('/leave', [AgencyController::class, 'leaveAgency']);
    });
