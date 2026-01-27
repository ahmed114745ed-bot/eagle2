<?php

use Illuminate\Support\Facades\Route;
use Utd\Room\Http\Controllers\RoomController;
use Utd\Room\Http\Controllers\BackgroundController;
use Utd\Room\Http\Controllers\MicrophoneController;
use Utd\Room\Http\Controllers\RoomCategoryController;

/*
|--------------------------------------------------------------------------
| Room API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RoomServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

Route::prefix('room')->group(function () {
    // Room Categories
    Route::get('categories', [RoomCategoryController::class, 'index']);
    Route::get('categories/types', [RoomCategoryController::class, 'byType']);
    Route::get('categories/{id}', [RoomCategoryController::class, 'show']);

    // Backgrounds
    Route::get('backgrounds', [BackgroundController::class, 'index']);
});

Route::middleware(['auth:sanctum'])->prefix('room')->group(function () {
    // Rooms
    Route::get('/', [RoomController::class, 'index']);
    Route::get('/mine', [RoomController::class, 'myRoom']);
    Route::post('/', [RoomController::class, 'store']);
    Route::get('/{id}', [RoomController::class, 'show']);
    Route::put('/{id}', [RoomController::class, 'update']);
    Route::get('/{id}/admins', [RoomController::class, 'admins']);
    Route::post('/{id}/toggle-writing', [RoomController::class, 'toggleWriting']);

    // Microphones
    Route::get('/{roomId}/microphones', [MicrophoneController::class, 'index']);
    Route::post('/{roomId}/microphones/assign', [MicrophoneController::class, 'assign']);
    Route::post('/{roomId}/microphones/remove', [MicrophoneController::class, 'remove']);
    Route::post('/{roomId}/microphones/status', [MicrophoneController::class, 'updateStatus']);

    // Backgrounds
    Route::post('/{roomId}/background', [BackgroundController::class, 'setBackground']);
});
