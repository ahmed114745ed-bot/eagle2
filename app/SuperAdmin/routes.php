<?php

use App\SuperAdmin\Controllers\AgencyController;
use App\SuperAdmin\Controllers\AgencyUserController;
use App\SuperAdmin\Controllers\AppearChargerAgencyController;
use App\SuperAdmin\Controllers\HomeCarouselController;
use Illuminate\Support\Facades\Route;
use KevinSoft\MultiLanguage\MultiLanguage;
use App\SuperAdmin\Controllers\BdController;
use App\SuperAdmin\Controllers\AuthController;
use App\SuperAdmin\Controllers\HomeController;
use App\SuperAdmin\Controllers\RoomController;
use App\SuperAdmin\Controllers\UserController;
use App\SuperAdmin\Controllers\ChargeController;
use App\SuperAdmin\Controllers\WalletController;
use App\SuperAdmin\Controllers\LiveRoomController;
use App\SuperAdmin\Controllers\BdSalariesController;
use App\SuperAdmin\Controllers\MultiLanguageController;
use App\SuperAdmin\Controllers\RequestAgencyController;

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
       // 'as' => 'superadmin.',
    ],
    function () {
        if (MultiLanguage::config("show-login-page", true)) {
            Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
        }
        Route::post('login', [AuthController::class, 'postLogin']);
        Route::get('logout', [AuthController::class, 'logout']);
        Route::Post('send-whatsapp-code', [AuthController::class, 'sendCodeWhatsapp']);
      //  Route::get('change-password-view', [AuthController::class, 'changePasswordView'])->name('superadmin.change-password-view');
        Route::get('change-password-view', [AuthController::class, 'changePasswordView']);
        Route::get('verify-whatsapp-code', [AuthController::class, 'verifyWhatsappCode'])
        ->name('verify-whatsapp-code');
    
        Route::post('change-password', [AuthController::class, 'changePassword'])->name('superadmin.change-password');

        Route::post('send-whatsapp-code-preview', [AuthController::class, 'send_whatsapp_code_preview'])
        ->name('superadmin.send-whatsapp-code-preview');

    }
);

Route::group([
    'prefix' => 'superadmin',
    'middleware' => [
        'web',
        'multiLanguage',
    ],
], function () {
    
    Route::get('change-password-view', [AuthController::class, 'changePasswordView']);

});


Route::group(
    [
        'prefix' => 'superadmin',
        'namespace' => 'App\\SuperAdmin\\Controllers',
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
        Route::resource('/salaries', BdSalariesController::class);
        Route::resource('/charges', ChargeController::class);
        // Route::resource('/wallet', 'WalletController');
        Route::post('salary/transfer', [WalletController::class, 'transfer'])->name('salary.transfer');
        Route::post('/locale', MultiLanguageController::class . '@locale');

        Route::resource('usersBd', BdController::class);

        //agencies
        Route::resource('/agencies', AgencyController::class);
        Route::resource('charge-agencies', AppearChargerAgencyController::class)->middleware('web-agency-feature');
        Route::get('shipping-agencies/profile/{id}', [AppearChargerAgencyController::class, 'shippingProfile'])->name('shipping.agency.profile');
        Route::get('agencies/profile/{id}', [AgencyController::class, 'profile'])->name('agency.profile');
        Route::resource('/request-agencies', RequestAgencyController::class);
        Route::prefix('ag')->name('agency.')->middleware('web-agency-feature')->group(function () {
            Route::resource('users', AgencyUserController::class);
            Route::get('professional/users', [AgencyUserController::class, 'indexProfessionals']);
        });
        Route::resource('live-rooms', LiveRoomController::class);

        //users
        Route::resource('users', 'UserController', [
            'names' => [
                'index' => 'users',
                'show' => 'users.show'
            ]
        ]);

        Route::resource('rooms', RoomController::class);
        Route::resource('home-carousel', HomeCarouselController::class);
        Route::get('users/profile/{id}', [UserController::class, 'show'])->name('user.profile');
        Route::get('users/{id}/same-device-users-table', [UserController::class, 'ajaxSameDeviceUsersTable']);

        Route::get('/charges', [ChargeController::class, 'index'])->name('charges');
        Route::post('wallet/charge', [WalletController::class, 'charge'])->name('wallet.charge');
  
        Route::get('peak-hours', [HomeController::class, 'peakHours'])->name('admin.peak-hours');

    }
);
