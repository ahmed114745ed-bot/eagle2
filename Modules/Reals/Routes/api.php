<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware(['auth:sanctum', 'checkLatestToken', 'generalBan','userBan'])->group (
    function (){

        Route::prefix('/')->middleware("appFeatureEnable:reel")->group(function (){
            Route::get('reals/user/{user_id?}', 'RealsController@getUserReals');
            Route::get('reals/user-followers', 'RealsController@getUserFollowersReals');
            Route::apiResource('/reals', 'RealsController');
            Route::apiResource('reals/{real_id}/comment', 'RealsUserCommentController');
            Route::apiResource('reals/{real_id}/like', 'RealsUserLikesController');
            Route::apiResource('/report', 'ReportController');
        });

    }
);
