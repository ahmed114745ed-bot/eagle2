<?php

use Illuminate\Http\Request;
use Modules\TaskStream\Http\Controllers\TaskStreamController;

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

Route::group([
    'prefix' => 'v1/task-stream',
    'middleware' => ['auth:sanctum', 'checkLatestToken', 'generalBan', 'localization' ,'update.last.seen']
], function (){
    Route::get('', [TaskStreamController::class, 'index']);
    Route::post('create', [TaskStreamController::class, 'store']);
    Route::post('join', [TaskStreamController::class, 'join']);
    Route::post('host-leave', [TaskStreamController::class, 'leave']);
    Route::post('send-invitation', [TaskStreamController::class, 'sendInvitation']);
    Route::post('respond-invitation', [TaskStreamController::class, 'respondInvitation']);
});

Route::group([
    'prefix' => 'v1/friends',
    'middleware' => ['auth:sanctum', 'checkLatestToken', 'generalBan', 'localization' ,'update.last.seen']
], function (){
    Route::get('online', [TaskStreamController::class, 'liveFriends']);
});
