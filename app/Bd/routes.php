<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use App\Bd\Controllers\HomeController;


use App\Bd\Controllers\WalletController;
// use App\Bd\Controllers\RequestAgencyController;
// use App\Bd\Controllers\ChargeController;
// use App\Bd\Controllers\BdSalariesController;
use App\Bd\Controllers\AgencyController;
use KevinSoft\MultiLanguage\MultiLanguage;
use App\Bd\Controllers\RequestAgencyController;




Route::prefix('bd')->name('bd.')->group(function () {
    Route::post('logout', [\App\Bd\Controllers\AuthController::class, 'logout'])->name('logout');

    // Route::middleware(['auth:bd'])->group(function () {
    //     Route::get('/', [\App\Bd\Controllers\DashboardController::class, 'index'])->name('dashboard');
    // });
});

Route::group(
    [
        'prefix' => 'bd',
        'namespace' => '',
        'middleware' => [
            'web',
            'multiLanguage',
        ],
        'as' => 'bd.',
    ],
    function () {
        if (MultiLanguage::config("show-login-page", true)) {
            Route::get('login', [\App\Bd\Controllers\AuthController::class, 'showLoginForm'])->name('login');
        }
        Route::post('login', [\App\Bd\Controllers\AuthController::class, 'postLogin']);
        Route::get('logout', [\App\Bd\Controllers\AuthController::class, 'logout']);
    }
);

Route::group(
    [
        'prefix' => 'bd',
        'namespace' => 'App\\Bd\\Controllers',
        'middleware' => [
            'web',
            'admin.auth',
            'admin.pjax',
            'admin.log',
            // 'admin.bootstrap',
            // 'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
        ],
        'as' => 'bd.',
    ],
    function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('/charges', [\App\Bd\Controllers\ChargeController::class, 'index'])->name('charges');
        Route::resource('/agencies', AgencyController::class);
        Route::resource('/salaries', \App\Bd\Controllers\BdSalariesController::class);
        Route::resource('/charges', \App\Bd\Controllers\ChargeController::class);
        // Route::resource('/wallet', 'WalletController');
        Route::post('admin/wallet/charge', [WalletController::class, 'charge'])->name('wallet.charge');
        Route::post('admin/salary/transfer', [WalletController::class, 'transfer'])->name('salary.transfer');

        Route::resource('/request-agencies', RequestAgencyController::class);
    }
);
