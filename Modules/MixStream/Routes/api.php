<?php

use Modules\MixStream\Http\Controllers\MixStreamController;

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
    'prefix' => 'v1/mix-stream',
    'middleware' => ['auth:sanctum', 'checkLatestToken', 'generalBan', 'localization' ,'update.last.seen']
], function (){
    Route::get('', [MixStreamController::class, 'index']);
    Route::post('create', [MixStreamController::class, 'store']);
    Route::post('join', [MixStreamController::class, 'join']);
    Route::post('host-leave', [MixStreamController::class, 'leave']);
    Route::post('send-invitation', [MixStreamController::class, 'sendInvitation']);
    Route::post('respond-invitation', [MixStreamController::class, 'respondInvitation']);
});
