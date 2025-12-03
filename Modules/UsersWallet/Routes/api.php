<?php

use Illuminate\Http\Request;
use Modules\UsersWallet\Http\Controllers\Api\UsersWalletController;
use Modules\UsersWallet\Http\Controllers\Api\WalletController;

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
    Route::post('/transfer', [UsersWalletController::class, 'transferToUser']);
    Route::get('/getTemplate', [WalletController::class, 'getTemplate']);
    Route::get('/transactions', [WalletController::class, 'getWalletTransactions']);
    Route::get('diamonds-statistic', [WalletController::class, 'diamondsStatistic']);
    Route::get('history', [WalletController::class, 'history']);

});


 