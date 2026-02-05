<?php

use Illuminate\Support\Facades\Route;
use Utd\Reals\Http\Controllers\RealsController;
use Utd\Reals\Http\Controllers\RealsUserLikesController;
use Utd\Reals\Http\Controllers\RealsUserCommentController;
use Utd\Reals\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Reals API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'checkLatestToken', 'generalBan', 'userBan', 'update.last.seen'])
    ->group(function () {

        Route::prefix('reals')->group(function () {

            // قراءة ريلز المستخدم
            Route::get('user/{user_id?}', [RealsController::class, 'getUserReals'])
                ->middleware('ban.user.actions:reals/user');

            // ريلزي
            Route::get('my-reals', [RealsController::class, 'getMyReals']);

            // ريلز المتابَعين
            Route::get('user-followers', [RealsController::class, 'getUserFollowersReals']);

            // CRUD للريلز
            Route::apiResource('', RealsController::class)
                ->middleware('ban.user.actions:reals')
                ->parameters(['' => 'real']);

            // تحديث ريل
            Route::post('update/{id}', [RealsController::class, 'update']);

            // التعليقات
            Route::apiResource('{real_id}/comment', RealsUserCommentController::class)
                ->names([
                    'index' => 'reals.comment.index',
                    'store' => 'reals.comment.store',
                    'show' => 'reals.comment.show',
                    'update' => 'reals.comment.update',
                    'destroy' => 'reals.comment.destroy',
                ]);

            // الإعجابات
            Route::apiResource('{real_id}/like', RealsUserLikesController::class)
                ->names([
                    'index' => 'reals.like.index',
                    'store' => 'reals.like.store',
                    'show' => 'reals.like.show',
                    'update' => 'reals.like.update',
                    'destroy' => 'reals.like.destroy',
                ]);
        });

        // التقارير
        Route::apiResource('report', ReportController::class);
    });
