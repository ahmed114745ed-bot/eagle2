<?php

use App\AreaManager\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;
use KevinSoft\MultiLanguage\MultiLanguage;
use App\AreaManager\Controllers\BdController;
use App\AreaManager\Controllers\AuthController;
use App\AreaManager\Controllers\HomeController;
use App\AreaManager\Controllers\RoomController;
use App\AreaManager\Controllers\UserController;
use App\AreaManager\Controllers\AgencyController;
use App\AreaManager\Controllers\ChargeController;
use App\AreaManager\Controllers\WalletController;
use App\AreaManager\Controllers\LiveRoomController;
use App\AreaManager\Controllers\AgencyUserController;
use App\AreaManager\Controllers\BdSalariesController;
use App\AreaManager\Controllers\HomeCarouselController;
use App\AreaManager\Controllers\MultiLanguageController;
use App\AreaManager\Controllers\RequestAgencyController;
use App\AreaManager\Controllers\ProfessionalBdController;
use App\AreaManager\Controllers\AreaManagerRewardController;
use App\AreaManager\Controllers\AppearChargerAgencyController;
use App\AreaManager\Controllers\AreaManagerBannerHistoryController;

Route::prefix('areaManager')->name('areaManager.')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});


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
        'namespace' => 'App\\AreaManager\\Controllers',
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
//        Route::get('setting', [AuthController::class, 'getSetting']);
//        Route::put('update-setting', [AuthController::class, 'putSetting']);
//
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::resource('superadmin-users', SuperAdminController::class);

//        Route::resource('/salaries', BdSalariesController::class);
//        Route::resource('/charges', ChargeController::class);
//        // Route::resource('/wallet', 'WalletController');
//        Route::post('salary/transfer', [WalletController::class, 'transfer'])->name('salary.transfer');
//        Route::post('/locale', MultiLanguageController::class . '@locale');
//
//        Route::resource('usersBd', BdController::class);
//
//        //agencies
//        Route::resource('/agencies', AgencyController::class);
//        Route::resource('charge-agencies', AppearChargerAgencyController::class)->middleware('web-agency-feature');
//        Route::get('shipping-agencies/profile/{id}', [AppearChargerAgencyController::class, 'shippingProfile'])->name('shipping.agency.profile');
//        Route::get('agencies/profile/{id}', [AgencyController::class, 'profile'])->name('agency.profile');
//        Route::resource('/request-agencies', RequestAgencyController::class);
//        Route::prefix('ag')->name('agency.')->middleware('web-agency-feature')->group(function () {
//            Route::resource('users', AgencyUserController::class);
//            Route::get('professional/users', [AgencyUserController::class, 'indexProfessionals']);
//        });
//        Route::resource('live-rooms', LiveRoomController::class);
//
//        //users
       Route::resource('users', 'UserController', [
           'names' => [
               'index' => 'users',
               'show' => 'users.show'
           ]
       ]);
//
//        Route::resource('rooms', RoomController::class);
//        Route::get('home-carousel/history', [SuperadminBannerHistoryController::class, 'index'])->name('home-carousel.history');
//
//        Route::resource('home-carousel', HomeCarouselController::class);
//
//        Route::get('users/profile/{id}', [UserController::class, 'show'])->name('user.profile');
//        Route::get('users/{id}/same-device-users-table', [UserController::class, 'ajaxSameDeviceUsersTable']);
//
//        Route::get('/charges', [ChargeController::class, 'index'])->name('charges');
//        Route::post('wallet/charge', [WalletController::class, 'charge'])->name('wallet.charge');
//
//        Route::get('rooms-activity', [HomeController::class, 'roomsActivity'])->name('admin.rooms-activity');
//        Route::resource('professional-bd', ProfessionalBdController::class);
//
//        // ajax
//        Route::get('peak-hours', [HomeController::class, 'peakHours'])->name('admin.peak-hours');
//        Route::get('users-online-stats', [HomeController::class, 'onlineStats'])
//            ->name('users.online.stats');
//        Route::get('top-users-visits', [HomeController::class, 'topUsersVisits'])->name('top-users-visits');
//        Route::resource('super-admin-rewards', SuperAdminRewardController::class);
//        Route::post('banner-request/{banner}', [HomeCarouselController::class, 'storeBannerRequest']);
//        Route::post('home-carousel/resend-banner-request/{banner}', [HomeCarouselController::class, 'resendBannerRequest'])
//        ->name('banner.resend');
//
//        Route::prefix('notifications')->group(function () {
//            Route::get('count', [App\Http\Controllers\Dashboard\Notification\SuperAdminNotificationController::class, 'count']);
//            Route::get('list', [App\Http\Controllers\Dashboard\Notification\SuperAdminNotificationController::class, 'list']);
//            Route::post('mark-as-read/{id}', [App\Http\Controllers\Dashboard\Notification\SuperAdminNotificationController::class, 'markAsRead']);
//            Route::post('mark-all-read', [App\Http\Controllers\Dashboard\Notification\SuperAdminNotificationController::class, 'markAllRead']);
//            Route::get('grid', [ App\SuperAdmin\Controllers\NotificationController::class, 'index'])->name('notifications.grid');
//        });
//
//        Route::post('/save-fcm-token', function (Illuminate\Http\Request $request) {
//            $user = auth()->user();
//            $user->fcm_token = $request->token;
//            $user->save();
//            return response()->json(['status' => 'success']);
//        });
    }
);
