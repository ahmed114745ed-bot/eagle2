<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Route::prefix('userswallet')->group(function() {
//     Route::get('/', 'UsersWalletController@index');
// });

use Modules\UsersWallet\Http\Controllers\Web\UserWithdrawalController;

Route::group(
    [
        'prefix'     => config('admin.route.prefix'),
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            'multiLanguage',
        ],
        'as' => config('admin.route.prefix') . '.',
    ],
    function () {
        Route::resource('wallet-withdrawal', UserWithdrawalController::class);

        
        Route::post('/wallet/withdrawals/{id}/approve', [UserWithdrawalController::class,'approve'])->name('withdrawals.approve');
        Route::post('/wallet/withdrawals/{id}/reject', [UserWithdrawalController::class,'reject'])->name('withdrawals.reject');

    
    });