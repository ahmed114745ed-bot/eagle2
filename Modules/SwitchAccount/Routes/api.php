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
    Route::post('add-account',[\Modules\SwitchAccount\Http\Controllers\SwitchAccountController::class,'add_account']);
    Route::post('switch-account',[\Modules\SwitchAccount\Http\Controllers\SwitchAccountController::class,'switch_account']);
    Route::get('my-accounts',[\Modules\SwitchAccount\Http\Controllers\SwitchAccountController::class,'myAccounts']);
});
