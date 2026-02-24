<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Utd\Tasks\Http\Controllers\DayTasksController;
use Utd\Tasks\Http\Controllers\TaskCompleteController;
use Utd\Tasks\Http\Controllers\TaskProgressController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/tasks', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'update.last.seen'])->group(function () {
    Route::get('days', [TaskProgressController::class, 'getDays']);
    Route::post('tasks/{taskId}/collect', [TaskCompleteController::class, 'collectTaskPoints']);
    Route::post('day/tasks/{taskId}', [DayTasksController::class, 'getDayTasks']);
});
