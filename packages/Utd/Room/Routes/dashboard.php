<?php

use Illuminate\Support\Facades\Route;
use Utd\Room\Http\Controllers\Dashboard\AdminBackgroundRoomController;

/*
|--------------------------------------------------------------------------
| Room Dashboard Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Background
    Route::controller(AdminBackgroundRoomController::class)->group(function () {
        Route::resource('room-background', AdminBackgroundRoomController::class);
        Route::get('/enable-room-background/{id}/{status}', 'enable_Background');
        Route::get('/Sort-background/{main_type}', 'sort');
        Route::post('/Change-Sort-background', 'change_sort');
    });
});
