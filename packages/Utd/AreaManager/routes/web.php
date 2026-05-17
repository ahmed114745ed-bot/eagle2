<?php

use KevinSoft\MultiLanguage\MultiLanguage;
use App\Helpers\AgencyPackageHelper;

use Utd\AreaManager\Http\Controllers\BdController;
use Utd\AreaManager\Http\Controllers\AuthController;
use Utd\AreaManager\Http\Controllers\HomeController;
use Utd\AreaManager\Http\Controllers\RoleController;
use Utd\AreaManager\Http\Controllers\RoomController;
use Utd\AreaManager\Http\Controllers\UserController;
use Utd\AreaManager\Http\Controllers\ChargeController;
use Utd\AreaManager\Http\Controllers\WalletController;
use Utd\AreaManager\Http\Controllers\LiveRoomController;
use Utd\AreaManager\Http\Controllers\AdminUserController;
use Utd\AreaManager\Http\Controllers\BdSalariesController;
use Utd\AreaManager\Http\Controllers\SuperAdminController;
use Utd\AreaManager\Http\Controllers\AdminRewardController;
use Utd\AreaManager\Http\Controllers\ProfessionalBdController;
use Utd\AreaManager\Http\Controllers\OfficialMessageController;
use Utd\AreaManager\Http\Controllers\DedicateRewardHistoryController;
use Utd\AreaManager\Http\Controllers\Admin\AreaManagerChargeController;
use Utd\AreaManager\Http\Controllers\Admin\AreaManagerChargeReportController;
use Utd\AreaManager\Http\Controllers\Admin\AreaManagerController as AdminAreaManagerController;

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

/*============================= DASHBOARD ROUTE THAT SPECIAL owner DASH ==============================*/

Route::group(
    [
        'prefix' => config('admin.route.prefix'),
        'namespace' => '',
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

        Route::get('area-manager-users/profile', [AdminAreaManagerController::class, 'showPreview']);
        Route::get('area-manager-charges', [AreaManagerChargeController::class, 'index']);
        Route::group(['prefix' => 'area-manager-charges-report'], function () {
            Route::get('/{id}', [AreaManagerChargeReportController::class, 'index']);
        });
    }
);
/*============================= DASHBOARD ROUTE THAT SPECIAL AREA MANGER DASH ==============================*/
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
        'namespace' => '',
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
        Route::get('area-manager-users/profile/{id}', [AdminAreaManagerController::class, 'showProfile']);
        Route::get('area-manager-users/{id}', [AdminAreaManagerController::class, 'showProfile']);
        Route::get('sub-area-manager-users/profile/{id}', [AdminUserController::class, 'showProfile']);

        Route::resource('superadmin-users', SuperAdminController::class);
        Route::get('superadmin-users-profile/{id}', [SuperAdminController::class, 'profile']);
        Route::get('users/profile/{id}', [UserController::class, 'show'])->name('user.profile');
        Route::resource('/bd-salaries', BdSalariesController::class);
        Route::resource('user-Bds', BdController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('auth-users', AdminUserController::class);

        //        //agencies - only if package installed
        if (AgencyPackageHelper::isAgencyInstalled()) {
            Route::resource('/agencies', \Utd\AreaManager\Http\Controllers\AgencyController::class);
            Route::resource('charge-agencies', \Utd\AreaManager\Http\Controllers\AppearChargerAgencyController::class)->middleware('web-agency-feature');
            Route::get('profile-shipping-agency/{id}', [\Utd\AreaManager\Http\Controllers\AppearChargerAgencyController::class, 'shippingProfile'])->name('shipping.agency.profile');
            Route::get('profile-agency/{id}', [\Utd\AreaManager\Http\Controllers\AgencyController::class, 'profile'])->name('agency.profile');
            //        Route::resource('/request-agencies', RequestAgencyController::class);
            Route::prefix('ag')->name('agency.')->middleware('web-agency-feature')->group(function () {
                Route::resource('users', \Utd\AreaManager\Http\Controllers\AgencyUserController::class);
                Route::get('professional/users', [\Utd\AreaManager\Http\Controllers\AgencyUserController::class, 'indexProfessionals']);
            });
        }
        Route::resource('live-rooms', LiveRoomController::class);
        Route::resource('official-message', OfficialMessageController::class);

        //        //users
        Route::resource('users', 'UserController', [
            'names' => [
                'index' => 'users',
                'show' => 'users.show'
            ]
        ]);
        Route::resource('rewards', AdminRewardController::class);
        Route::get('search/super-admin', [AdminRewardController::class, 'getSuperAdmins'])->name('super-admin');

        Route::resource('rewards-history', DedicateRewardHistoryController::class);
        //
        Route::resource('rooms', RoomController::class);

        Route::get('users/{id}/same-device-users-table', [UserController::class, 'ajaxSameDeviceUsersTable']);
        //
        Route::get('/charges', [ChargeController::class, 'index']);
        Route::post('wallet/charge', [WalletController::class, 'charge'])->name('wallet.charge');

        Route::get('rooms-activity', [HomeController::class, 'roomsActivity'])->name('admin.rooms-activity');
        Route::resource('professional-bd', ProfessionalBdController::class);
        //
        //        // ajax
        Route::get('peak-hours', [HomeController::class, 'peakHours'])->name('admin.peak-hours');
        Route::get('users-online-stats', [HomeController::class, 'onlineStats'])->name('users.online.stats');
        Route::get('top-users-visits', [HomeController::class, 'topUsersVisits'])->name('top-users-visits');
        Route::get('/sub-area-managers', [ChargeController::class, 'subAreaManagers'])->name('sub.admins');

        Route::prefix('statistics')->name('statistics.')->group(function () {
            Route::get('top-users-data', [HomeController::class, 'topUsersData']);
            Route::get('comparison-user-signup', [HomeController::class, 'comparisonUserSignUp']);
            Route::get('distribution-rooms', [HomeController::class, 'distributionRooms']);
            Route::get('top-room-gifts', [HomeController::class, 'topRoomGifts']);
            Route::get('active-rooms', [HomeController::class, 'averageActiveRooms']);
            if (AgencyPackageHelper::isAgencyInstalled()) {
                Route::get('agency-target', [HomeController::class, 'agencyTarget']);
                Route::get('comparison-agencies-target', [HomeController::class, 'comparisonAgencyTarget']);
                Route::get('agency-stats', [HomeController::class, 'getStats']);
            }
            Route::get('top-sender', [HomeController::class, 'topSender']);
            Route::get('top-receiver', [HomeController::class, 'topReceiver']);
            Route::get('room-stats', [HomeController::class, 'roomStats']);
            Route::get('bd-stats', [HomeController::class, 'getBdStats']);
            Route::get('balance-data', [HomeController::class, 'getBalanceData']);
            Route::get('stats-data', [HomeController::class, 'getStatsData']);
            Route::get('top-followers', [HomeController::class, 'getTopFollowers']);
            Route::get('game-summary', [HomeController::class, 'gameSummary']);
            Route::get('peak-hours', [HomeController::class, 'peakHours'])->name('owner.peak-hours');
            Route::get('rooms-activity', [HomeController::class, 'roomsActivity'])->name('owner.rooms-activity');
            Route::get('top-users-visits', [HomeController::class, 'topUsersVisits'])->name('top-users-visits');
            Route::get('users-online-stats', [HomeController::class, 'onlineStats'])->name('users.online.stats');
        });
        Route::prefix('dashboard')->group(function () {
            Route::get('/finance/cards', [HomeController::class, 'financeCards']);
            Route::get('/finance/tables', [HomeController::class, 'financeTables']);
            Route::get('/finance/chart', [HomeController::class, 'financeChartIndex']);
            Route::get('wallet-logs/ajax', [HomeController::class, 'ajaxWalletLogs'])->name('wallet-logs.ajax');
        });
    }
);

Route::prefix('areaManager')->name('areaManager.')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

/*============================= End DASHBOARD ROUTE THAT SPECIAL AREA MANGER DASH ==============================*/
