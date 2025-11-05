<?php

use KevinSoft\MultiLanguage\MultiLanguage;

use Modules\AreaManager\Http\Controllers\BdController;
use Modules\AreaManager\Http\Controllers\AuthController;
use Modules\AreaManager\Http\Controllers\HomeController;
use Modules\AreaManager\Http\Controllers\RoleController;
use Modules\AreaManager\Http\Controllers\RoomController;
use Modules\AreaManager\Http\Controllers\UserController;
use Modules\AreaManager\Http\Controllers\AgencyController;
use Modules\AreaManager\Http\Controllers\ChargeController;
use Modules\AreaManager\Http\Controllers\WalletController;
use Modules\AreaManager\Http\Controllers\LiveRoomController;
use Modules\AreaManager\Http\Controllers\AdminUserController;
use Modules\AreaManager\Http\Controllers\AgencyUserController;

use Modules\AreaManager\Http\Controllers\BdSalariesController;
use Modules\AreaManager\Http\Controllers\SuperAdminController;
use Modules\AreaManager\Http\Controllers\ProfessionalBdController;
use Modules\AreaManager\Http\Controllers\OfficialMessageController;
use Modules\AreaManager\Http\Controllers\Admin\AreaManagerController as AdminAreaManagerController;
use Modules\AreaManager\Http\Controllers\AppearChargerAgencyController;
use Modules\AreaManager\Http\Controllers\Admin\AreaManagerChargeController;
use Modules\AreaManager\Http\Controllers\Admin\AreaManagerChargeReportController;

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

Route::prefix('areaManager')->name('areaManager.')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});



Route::group(
    [
        'prefix' => config('admin.route.prefix'),
        'namespace' => 'Modules\\AreaManager\\Http\\Controllers\\Admin',
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            'multiLanguage',
        ],
        'as' => config('admin.route.prefix') . '.',
    ],
    function () {
        Route::resource('area-manager-users', AdminAreaManagerController::class);
       

        Route::get('area-manager-charges', [AreaManagerChargeController::class, 'index']);
        Route::group(['prefix' => 'area-manager-charges-report'], function () {
            Route::get('/{id}', [AreaManagerChargeReportController::class, 'index']);
        });
    }
);

Route::group(
    [
        'prefix' => 'areaManager',
        'namespace' => '',
        'middleware' => [
            'web',
            'multiLanguage',
        ],
        'as' => 'areaManager.',
    ],
    function () {
        if (MultiLanguage::config("show-login-page", true)) {
            Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
        }
        Route::post('login', [AuthController::class, 'postLogin']);
        Route::get('logout', [AuthController::class, 'logout']);
        Route::Post('send-whatsapp-code', [AuthController::class, 'sendCodeWhatsapp']);
        Route::get('change-password-view', [AuthController::class, 'changePasswordView']);
        Route::get('verify-whatsapp-code', [AuthController::class, 'verifyWhatsappCode'])
            ->name('verify-whatsapp-code');

        Route::post('change-password', [AuthController::class, 'changePassword'])->name('superadmin.change-password');

        Route::post('send-whatsapp-code-preview', [AuthController::class, 'send_whatsapp_code_preview'])
            ->name('superadmin.send-whatsapp-code-preview');
    }
);

Route::group(
    [
        'prefix' => 'areaManager',
        'namespace' => 'Modules\\AreaManager\\Http\\Controllers',
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
        'as' => 'areaManager.',
    ],
    function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::resource('superadmin-users', SuperAdminController::class);
        Route::get('users/profile/{id}', [UserController::class, 'show'])->name('user.profile');
        Route::resource('/bd-salaries', BdSalariesController::class);
        Route::resource('user-Bds', BdController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('auth-users', AdminUserController::class);

        //        //agencies
        Route::resource('/agencies', AgencyController::class);
        Route::resource('charge-agencies', AppearChargerAgencyController::class)->middleware('web-agency-feature');
        Route::get('profile-shipping-agency/{id}', [AppearChargerAgencyController::class, 'shippingProfile'])->name('shipping.agency.profile');
        Route::get('profile-agency/{id}', [AgencyController::class, 'profile'])->name('agency.profile');
        //        Route::resource('/request-agencies', RequestAgencyController::class);
        Route::prefix('ag')->name('agency.')->middleware('web-agency-feature')->group(function () {
            Route::resource('users', AgencyUserController::class);
            Route::get('professional/users', [AgencyUserController::class, 'indexProfessionals']);
        });
        Route::resource('live-rooms', LiveRoomController::class);
        Route::resource('official-message', OfficialMessageController::class);

        //        //users
        Route::resource('users', 'UserController', [
            'names' => [
                'index' => 'users',
                'show' => 'users.show'
            ]
        ]);
        //
        Route::resource('rooms', RoomController::class);

        Route::get('users/{id}/same-device-users-table', [UserController::class, 'ajaxSameDeviceUsersTable']);
        //
        Route::get('/charges', [ChargeController::class, 'index'])->name('charges');
        Route::post('wallet/charge', [WalletController::class, 'charge'])->name('wallet.charge');

        Route::get('rooms-activity', [HomeController::class, 'roomsActivity'])->name('admin.rooms-activity');
        Route::resource('professional-bd', ProfessionalBdController::class);
        //
        //        // ajax
        Route::get('peak-hours', [HomeController::class, 'peakHours'])->name('admin.peak-hours');
        Route::get('users-online-stats', [HomeController::class, 'onlineStats'])->name('users.online.stats');
        Route::get('top-users-visits', [HomeController::class, 'topUsersVisits'])->name('top-users-visits');
        Route::get('/sub-area-managers', [ChargeController::class, 'subAreaManagers'])->name('sub.admins');
    }
);
