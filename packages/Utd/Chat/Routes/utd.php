<?php

use Illuminate\Support\Facades\Route;
use Utd\Chat\Http\Controllers\Utd\GroupChatController;

Route::group([
    'middleware' => ['api', 'localization'],
    'prefix' => 'api/utd',
], function () {
    Route::prefix('group-chat')->group(function () {
        Route::get('/', [GroupChatController::class, 'index']);
        Route::post('/', [GroupChatController::class, 'store']);
        Route::post('/add-experience-points', [GroupChatController::class, 'add_experience_points']);
        Route::post('/update/{id}', [GroupChatController::class, 'update']);
        Route::post('/delete/{id}', [GroupChatController::class, 'destroy']);
        Route::get('/{id}', [GroupChatController::class, 'show']);
    });

});
