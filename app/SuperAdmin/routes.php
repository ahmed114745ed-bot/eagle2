<?php

use App\SuperAdmin\Controllers\AgencyController;
use App\SuperAdmin\Controllers\AuthController;
use App\SuperAdmin\Controllers\BdSalariesController;
use App\SuperAdmin\Controllers\ChargeController;
use App\SuperAdmin\Controllers\HomeController;
use App\SuperAdmin\Controllers\MultiLanguageController;
use App\SuperAdmin\Controllers\RequestAgencyController;
use App\SuperAdmin\Controllers\UserController;
use App\SuperAdmin\Controllers\WalletController;
use Illuminate\Support\Facades\Route;
use KevinSoft\MultiLanguage\MultiLanguage;




Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

Route::group(
    [
        'prefix' => 'superadmin',
        'namespace' => '',
        'middleware' => [
            'web',
            'multiLanguage',
        ],
        'as' => 'superadmin.',
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
        'prefix' => 'superadmin',
        'namespace' => 'App\\superadmin\\Controllers',
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
        'as' => 'superadmin.',
    ],
    function () {
        Route::get('setting', [AuthController::class, 'getSetting']);
        Route::put('update-setting', [AuthController::class, 'putSetting']);

        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('/charges', [ChargeController::class, 'index'])->name('charges');
        Route::resource('/agencies', AgencyController::class);
        Route::resource('/salaries', BdSalariesController::class);
        Route::resource('/charges', ChargeController::class);
        // Route::resource('/wallet', 'WalletController');
        Route::post('wallet/charge', [WalletController::class, 'charge'])->name('wallet.charge');
        Route::post('salary/transfer', [WalletController::class, 'transfer'])->name('salary.transfer');
        Route::get('agencies/profile/{id}', [AgencyController::class, 'profile'])->name('agency.profile');
        Route::get('users/profile/{id}', [UserController::class, 'show'])->name('user.profile');

        Route::post('/locale', MultiLanguageController::class . '@locale');

        Route::resource('/request-agencies', RequestAgencyController::class);
    }
);
