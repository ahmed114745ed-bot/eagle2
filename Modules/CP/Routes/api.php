<?php

use App\Models\Ware;
use Illuminate\Http\Request;
use Modules\CP\Http\Controllers\Api\CpRelationController;
use Modules\CP\Http\Controllers\Api\CpController;

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

Route::middleware(['auth:sanctum','appFeatureEnable:achievement'])->group(function () {
    Route::get('/cp-relations', [CpRelationController::class,'index']);
    Route::post('/make-cp-request', [CpController::class,'makeRequestCp']);
    Route::get('/get-cp-request', [CpController::class,'getRequestCp']);
    Route::post('/respond-request', [CpController::class,'RespondRequest']);
    Route::get('/cp-ranking', [CpController::class,'CpRanking']);
    Route::get('/cp-list', [CpController::class,'cpList']);
    Route::post('/buy-sets', [CpController::class,'extendCard']);
    Route::get('/cp-profile', [CpController::class,'cpProfile']);

});
