<?php 
use Illuminate\Support\Facades\Route;
use KevinSoft\MultiLanguage\MultiLanguage;
use Illuminate\Routing\Router;


use App\Bd\Controllers\HomeController;
use App\Bd\Controllers\RequestAgencyController;
use App\Bd\Controllers\ChargeController;
// use App\Bd\Controllers\BdSalariesController;
// use App\Bd\Controllers\AgencyController;
// use App\Bd\Controllers\WalletController;




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
    function (Router $router) {
        if (MultiLanguage::config("show-login-page", true)) {
            Route::get('login', [\App\Bd\Controllers\AuthController::class, 'showLoginForm'])->name('login');
        }
        Route::post('login', [\App\Bd\Controllers\AuthController::class, 'postLogin']);
      
    }

);

Route::group(
    [
        'prefix' => 'bd',
        'namespace' => 'App\\Bd\\Controllers',
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
        ],
        'as' => 'bd.',
    ],
    function (Router $router) {

                $router->get('/', [HomeController::class,'index'])->name('home');
                $router->get('/charges', [ChargeController::class, 'index'])->name('charges');
                $router->resource('/agencies', AgencyController::class);
                $router->resource('/salaries', BdSalariesController::class);
                $router->resource('/charges', ChargeController::class);
                // $router->resource('/wallet', 'WalletController');
                Route::post('admin/wallet/charge', [WalletController::class, 'charge'])->name('wallet.charge');
                Route::post('admin/salary/transfer', [WalletController::class, 'transfer'])->name('salary.transfer');

                $router->resource('/request-agencies', RequestAgencyController::class);

    });