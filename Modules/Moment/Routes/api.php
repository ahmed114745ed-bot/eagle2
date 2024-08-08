<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Moment\Http\Controllers\MomentController;
use Modules\Moment\Http\Controllers\MomentUserGiftsController;
use Modules\Moment\Http\Controllers\ReportController;

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

Route::middleware(['auth:sanctum', 'checkLatestToken', 'generalBan','userBan','appFeatureEnable:moment'])->group (
    function (){
            Route::apiResource('/moment', 'MomentController');
            Route::apiResource('moment/{moment_id}/comment', 'MomentUserCommentController');
            Route::apiResource('moment/{moment_id}/like', 'MomentUserLikesController');
            Route::apiResource('moment/{moment_id}/gift/', 'MomentUserGiftsController');
//            Route::apiResource('moment/{moment_id}/users/gifts/', 'MomentUserGiftsController');
            Route::post('moment/{moment_id}/report', [ReportController::class, 'store']);
            Route::get('moments/{id}/gifts',  [MomentUserGiftsController::class, 'getGifts']);




    }
);
