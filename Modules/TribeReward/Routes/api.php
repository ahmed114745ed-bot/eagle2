<?php

use Illuminate\Http\Request;
use Modules\TribeReward\Http\Controllers\Api\TribeController;

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
    'prefix' => 'tribes',
    'middleware' => ['auth:sanctum', 'checkLatestToken', 'generalBan']
], function (){
    Route::get('/general', [TribeController::class, 'index']);
});
