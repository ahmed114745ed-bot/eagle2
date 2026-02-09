<?php

use Illuminate\Support\Facades\Route;
use Utd\Pk\Http\Controllers\Dashboard\AdminPKEventsController;
use Utd\Pk\Http\Controllers\Dashboard\AdminPKEventsRewardsController;

/*
|--------------------------------------------------------------------------
| PK Dashboard Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the PkServiceProvider within a group for
| the Dashboard API routes.
|
*/

Route::group([
    'middleware' => ['api', 'auth:sanctum'],
    'prefix' => 'api/dashboard'
], function () {
    Route::resource('admin-event-pk', AdminPKEventsController::class);
    Route::resource('admin-event-pk-rewords', AdminPKEventsRewardsController::class);
});
