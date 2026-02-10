<?php

use Illuminate\Support\Facades\Route;
use Utd\Family\Http\Controllers\FamilyController;

Route::prefix('api/families')->group(function () {
    Route::get('all', [FamilyController::class, 'index']);
    Route::get('show/{id}', [FamilyController::class, 'show']);
    Route::post('create', [FamilyController::class, 'store']);
    Route::post('ranking', [FamilyController::class, 'ranking']);
    Route::post('top-ranking', [FamilyController::class, 'topUserRanking']);
    Route::post('edit/{id}', [FamilyController::class, 'update']);
    Route::post('join', [FamilyController::class, 'join']);
    Route::get('delete/{id}', [FamilyController::class, 'destroy']);
    Route::post('remove_user', [FamilyController::class, 'removeUser']);
    Route::post('req_list', [FamilyController::class, 'req_list']);
    Route::post('take_action', [FamilyController::class, 'RequestFamilyAction']);
    Route::post('change_user_type', [FamilyController::class, 'changeFamilyUserType']);
    Route::post('getMembersList', [FamilyController::class, 'getMembersList']);
    Route::post('getFamilyRooms', [FamilyController::class, 'getFamilyRooms']);
    Route::post('exitFamily', [FamilyController::class, 'exitFamily']);
});
