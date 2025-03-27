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
            Route::get('reals/my-reals', 'RealsController@getMyReals');
            Route::get('reals/user-followers', 'RealsController@getUserFollowersReals');
            Route::apiResource('/reals', 'RealsController');
            Route::post('reals-update/{id}', 'RealsController@update');
            Route::apiResource('reals/{real_id}/comment', 'RealsUserCommentController', [
                'names' => [
                    'index' => 'reals.comment.index',
                    'store' => 'reals.comment.store',
                    'show' => 'reals.comment.show',
                    'update' => 'reals.comment.update',
                    'destroy' => 'reals.comment.destroy',
                ]
            ]);
            Route::apiResource('reals/{real_id}/like', 'RealsUserLikesController', [
                'names' => [
                    'index' => 'reals.like.index',
                    'store' => 'reals.like.store',
                    'show' => 'reals.like.show',
                    'update' => 'reals.like.update',
                    'destroy' => 'reals.like.destroy',
                ]
            ]);
            Route::apiResource('/report', 'ReportController');
        });

    }
);
