<?php

use Illuminate\Http\Request;
use Modules\Tasks\Http\Controllers\TaskCompleteController;
use Modules\Tasks\Http\Controllers\TaskProgressController;

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

//include base_path('Modules/Tasks/Routes/api.php');

Route::middleware('auth:api')->get('/tasks', function (Request $request) {
    return $request->user();
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('user/{userId}/progress', [TaskProgressController::class, 'getUserProgress']);
    Route::post('tasks/{taskId}/collect', [TaskCompleteController::class, 'collectTaskPoints']);
});


//Route::get('user/{userId}/progress', [TaskProgressController::class, 'getUserProgress']);