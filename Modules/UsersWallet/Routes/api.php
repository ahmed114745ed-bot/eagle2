<?php

use Illuminate\Http\Request;
use Modules\UsersWallet\Http\Controllers\UsersWalletController;

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


Route::group(['prefix' => 'wallets', 'middleware' => ['auth:sanctum', 'checkLatestToken', 'generalBan', 'userBan', 'localization']], function (){
    Route::post('/withdraw', [UsersWalletController::class, 'requestWithdrawal'])->middleware('auth:sanctum');

});

