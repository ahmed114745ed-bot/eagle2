<?php

use Illuminate\Http\Request;

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

Route::middleware(['auth:sanctum', 'checkLatestToken', 'generalBan'])->group(function () {
    Route::post('buy-special-id',[\Modules\SpecialId\Http\Controllers\Api\SpecialIdController::class,'buySpecialId']);
    Route::post('use-special-id',[\Modules\SpecialId\Http\Controllers\Api\SpecialIdController::class,'usePackItem']);
    Route::post('upload-special-id',[\Modules\SpecialId\Http\Controllers\Api\SpecialIdController::class,'upload_special_id']);
    Route::get('special-frame',[\Modules\SpecialId\Http\Controllers\Api\SpecialIdController::class,'specialIdFrame']);
    Route::get('special-users',[\Modules\SpecialId\Http\Controllers\Api\SpecialIdController::class,'specialUsers']);
});
