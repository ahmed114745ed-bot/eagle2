<?php

use Utd\Moments\Http\Controllers\utd\MomentsController;
use Utd\Moments\Http\Controllers\utd\ReportMomentController;

Route::prefix('moment')->group(function () {
    Route::get('/', [MomentsController::class, 'all']);
    Route::get('/show/{id}', [MomentsController::class, 'show']);
    Route::post('/search/{uuid}', [MomentsController::class, 'search']);
    Route::delete('/delete/{id}', [MomentsController::class, 'destroy']);
    Route::post('/config', [MomentsController::class, 'config']);
    Route::get('/get-user-Moments/{id}', [MomentsController::class, 'get_user_moments']);
});

Route::prefix('report-moment')->group(function () {
    Route::get('/', [ReportMomentController::class, 'all']);
    Route::get('/show/{id}', [ReportMomentController::class, 'show']);
    Route::post('/create', [ReportMomentController::class, 'create']);
    Route::delete('/delete/{id}', [ReportMomentController::class, 'destroy']);
    Route::post('/update/{id}', [ReportMomentController::class, 'update']);
    Route::post('delete-moment/{moment_id}/{id}', [ReportMomentController::class, 'destroyDash']);
});
