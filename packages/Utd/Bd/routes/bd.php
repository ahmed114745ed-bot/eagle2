<?php

use Utd\Bd\Http\Controllers\AuthController;
use Utd\Bd\Http\Controllers\BdSalariesController;
use Utd\Bd\Http\Controllers\ChargeController;
use Utd\Bd\Http\Controllers\MultiLanguageController;
use Illuminate\Support\Facades\Route;
use Utd\Bd\Http\Controllers\HomeController;
use Utd\Bd\Http\Controllers\WalletController;
// use Utd\Bd\Http\Controllers\RequestAgencyController;
// use Utd\Bd\Http\Controllers\ChargeController;
// use Utd\Bd\Http\Controllers\BdSalariesController;
use Utd\Bd\Http\Controllers\UserController;
use KevinSoft\MultiLanguage\MultiLanguage;
use App\Helpers\AgencyPackageHelper;




Route::prefix('bd')->name('bd.')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Route::middleware(['auth:bd'])->group(function () {
    //     Route::get('/', [\Utd\Bd\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
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
        'namespace' => 'Utd\\Bd\\Http\\Controllers',
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
            Route::resource('/agencies', \Utd\Bd\Http\Controllers\AgencyController::class);
            Route::get('agencies/profile/{id}', [\Utd\Bd\Http\Controllers\AgencyController::class, 'profile'])->name('agency.profile');
            Route::resource('/request-agencies', \Utd\Bd\Http\Controllers\RequestAgencyController::class);
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
