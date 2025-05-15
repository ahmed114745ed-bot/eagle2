<?php

use Illuminate\Http\Request;
use Modules\Wallet\Http\Controllers\WalletController;

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

Route::group(['prefix' => 'wallet', 'middleware' => ['auth:sanctum', 'checkLatestToken', 'generalBan', 'userBan']], function (){
    Route::post('make_transfer', [WalletController::class, 'makeTransaction']);
});
