<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Utd\RoomCup\Http\Controllers\Admin\RoomCupTargetController;
use Utd\RoomCup\Http\Controllers\Admin\RoomCupSettingsController;
use Utd\RoomCup\Http\Controllers\Admin\RoomCupReportsController;

/*
|--------------------------------------------------------------------------
| Admin Web Routes
|--------------------------------------------------------------------------
*/

Route::resource('room-cup-target', RoomCupTargetController::class);
Route::resource('room-cup-settings', RoomCupSettingsController::class);
Route::post('room-cup-settings/save', [RoomCupSettingsController::class, 'save'])->name('room-cup-settings.save');
Route::resource('room-cup-reports', RoomCupReportsController::class);

// Public route for cup targets view
Route::get('/cup-targets-view', [RoomCupTargetController::class, 'cupTargetHtml'])->withoutMiddleware(['web', 'admin']);

// Manual trigger route (for testing)
Route::get('/roomcup/calculate-rewards', function () {
    Artisan::call('roomcup:calculate-rewards');
    $output = Artisan::output();
    return response()->json(['output' => $output]);
});
