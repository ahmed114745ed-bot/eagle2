<?php

use App\Bd\Controllers\AuthController;
use App\Bd\Controllers\BdSalariesController;
use App\Bd\Controllers\ChargeController;
use App\Bd\Controllers\MultiLanguageController;
use Illuminate\Support\Facades\Route;
use App\Bd\Controllers\HomeController;
use App\Bd\Controllers\WalletController;
// use App\Bd\Controllers\RequestAgencyController;
// use App\Bd\Controllers\ChargeController;
// use App\Bd\Controllers\BdSalariesController;
use App\Bd\Controllers\UserController;
use KevinSoft\MultiLanguage\MultiLanguage;
use App\Helpers\AgencyPackageHelper;




Route::prefix('bd')->name('bd.')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

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
            Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
        }
        Route::post('login', [AuthController::class, 'postLogin']);
        Route::get('logout', [AuthController::class, 'logout']);



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
            'admin.bootstrap',
            // 'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
        ],
        'as' => 'bd.',
    ],
    function () {
        Route::get('setting', [AuthController::class, 'getSetting']);
        Route::put('update-setting', [AuthController::class, 'putSetting']);

        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('/charges', [ChargeController::class, 'index'])->name('charges');
        
        // Agency routes - only if package installed
        if (AgencyPackageHelper::isAgencyInstalled()) {
            Route::resource('/agencies', \App\Bd\Controllers\AgencyController::class);
            Route::get('agencies/profile/{id}', [\App\Bd\Controllers\AgencyController::class, 'profile'])->name('agency.profile');
            Route::resource('/request-agencies', \App\Bd\Controllers\RequestAgencyController::class);
        }
        
        Route::resource('/salaries', BdSalariesController::class);
        Route::resource('/charges', ChargeController::class);
        // Route::resource('/wallet', 'WalletController');
        Route::post('wallet/charge', [WalletController::class, 'charge'])->name('wallet.charge');
        Route::post('salary/transfer', [WalletController::class, 'transfer'])->name('salary.transfer');
        Route::get('users/profile/{id}', [UserController::class, 'show'])->name('user.profile');

        Route::post('/locale', MultiLanguageController::class . '@locale');
    }
);
