<?php

use Illuminate\Http\Request;
use Modules\CP\Http\Controllers\Api\CPControllerApi;

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

Route::middleware('auth:api')->get('/cp', function (Request $request) {
    return $request->user();
});
Route::middleware(['auth:sanctum', 'checkLatestToken', 'generalBan','appFeatureEnable:cp'])->group(function () {
    Route::post('cp-ranking',[CPControllerApi::class,'cp_ranking']);
    Route::get('cp-level',[CPControllerApi::class,'cp_level']);
    Route::post('cancel-cp',[CPControllerApi::class,'cancel_cp']);

});