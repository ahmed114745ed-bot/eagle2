<?php

use Illuminate\Support\Facades\Route;
use Utd\Family\Http\Controllers\Api\FamilyController;
use Utd\Family\Http\Controllers\Api\FamilyLevelController;
use Utd\Family\Http\Controllers\Utd\FamilyController as UtdFamilyController;

$familiesApiMiddleware = config('family.route_middlewares.families_api', ['api']);
$utdApiMiddleware = config('family.route_middlewares.utd_api', $familiesApiMiddleware);
$familyLevelsMiddleware = config('family.route_middlewares.family_levels_api', $familiesApiMiddleware);

Route::prefix('api/families')->middleware($familiesApiMiddleware)->group(function () {
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


// Utd Custom Dashboard Routes
Route::prefix('utd/families')->middleware($utdApiMiddleware)->group(function () {
    Route::get('/', [UtdFamilyController::class, 'index']);
    Route::get('/all', [UtdFamilyController::class, 'all']);
    Route::post('/', [UtdFamilyController::class, 'store']);
    Route::post('/update/{id}', [UtdFamilyController::class, 'update']);
    Route::post('/delete/{id}', [UtdFamilyController::class, 'destroy']);
    Route::post('/delete-all', [UtdFamilyController::class, 'delete_all']);
    Route::get('/{id}', [UtdFamilyController::class, 'show']);
});

Route::prefix('utd/family-levels')->middleware($familyLevelsMiddleware)->group(function () {
    Route::get('/all', [FamilyLevelController::class, 'index']);
    Route::post('/show/{id}', [FamilyLevelController::class, 'show']);
    Route::post('/create', [FamilyLevelController::class, 'store']);
    Route::post('/update/{id}', [FamilyLevelController::class, 'update']);
    Route::post('/delete/{id}', [FamilyLevelController::class, 'destroy']);
});
