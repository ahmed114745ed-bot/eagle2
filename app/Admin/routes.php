<?php

use App\Models\Room;
use Illuminate\Routing\Router;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Route;
use App\Admin\Controllers\BoxController;
use App\Admin\Controllers\VipController;
use App\Admin\Controllers\CoinController;
use App\Admin\Controllers\OVipController;
use App\Admin\Controllers\ReelController;
use App\Admin\Controllers\RoomController;
use App\Admin\Controllers\ColorController;
use App\Admin\Controllers\OfferController;
use App\Admin\Controllers\RouteController;
use KevinSoft\MultiLanguage\MultiLanguage;
use App\Admin\Controllers\AgencyController;
use App\Admin\Controllers\BannerController;
use App\Admin\Controllers\CustomController;
use App\Admin\Controllers\ExportController;
use App\Admin\Controllers\MomentController;
use App\Admin\Controllers\PoliceController;
use App\Admin\Controllers\TargetController;
use App\Admin\Controllers\AgencyMangerUsers;
use App\Admin\Controllers\AllGameController;
use App\Admin\Controllers\BanTypeController;
use App\Admin\Controllers\RoomMicController;
use App\Admin\Controllers\RoomVipController;
use App\Admin\Controllers\SettingController;
use App\Admin\Controllers\WareTabController;
use App\Admin\Controllers\WareVipController;
use App\Http\Controllers\SettingsController;
use App\Admin\Controllers\LanguageController;
use App\Admin\Controllers\OvipGiftController;
use App\Admin\Controllers\QuestionController;
use App\Admin\Controllers\ScaffoldController;
use App\Admin\Controllers\TerminalController;
use App\Admin\Controllers\WithdrawController;
use App\Admin\Controllers\AdminAuthController;
use App\Admin\Controllers\ChargeVipController;
use App\Admin\Controllers\GroupChatController;
use App\Admin\Controllers\InterestsController;
use App\Admin\Controllers\UserLevelController;
use App\Admin\Controllers\AdminUsersController;
use App\Admin\Controllers\AppFeatureController;
use App\Admin\Controllers\ImageColorController;
use App\Admin\Controllers\PermissionController;
use App\Admin\Controllers\ReportUserController;
use App\Admin\Controllers\RoomTargetController;
use App\Admin\Controllers\TestPusherController;
use App\Admin\Controllers\CoreWalletsController;
use App\Admin\Controllers\OvipGiftTapController;
use App\Admin\Controllers\ParentUsersController;
use App\Admin\Controllers\PaymentCoinController;
use App\Admin\Controllers\ReportRealsController;

use App\Admin\Controllers\ChargeReportController;
use App\Admin\Controllers\ReelSettingsController;
use App\Admin\Controllers\ReportMomentController;
use App\Admin\Controllers\RoomSettingsController;
use App\Admin\Controllers\AgencySettingController;
use App\Admin\Controllers\DeleteAccountController;
use App\Admin\Controllers\MangerSettingController;
use App\Admin\Controllers\MultiLanguageController;
use App\Admin\Controllers\PaymentGetWayController;
use App\Admin\Controllers\PaymentMethodController;
use App\Admin\Controllers\ServerCountryController;
use App\Admin\Controllers\AgencySettingsController;
use App\Admin\Controllers\BlackListUsersController;
use App\Admin\Controllers\ChargesSettingController;
use App\Admin\Controllers\MomentSettingsController;
use App\Admin\Controllers\RoomGiftTargetController;
use App\Http\Controllers\AddTargetToJsonController;
use App\Admin\Controllers\chargUsersSleemController;
use App\Admin\Controllers\AgoraZegoSettingController;
use App\Admin\Controllers\AppSitiingCOnfigController;
use App\Admin\Controllers\GroupChatSettingController;
use App\Admin\Controllers\TargetPercentageController;
use App\Admin\Controllers\AdminAgencyMangerController;
use App\Admin\Controllers\CustomZegoMessageController;
use App\Admin\Controllers\GameChargeHistoryController;
use App\Admin\Controllers\UserOnlineHistoryController;
use App\Admin\Controllers\ChangeAgencyMangerController;
use App\Admin\Controllers\ChangeLevelHistoryController;
use App\Admin\Controllers\TrashedUserAccountController;
use App\Admin\Controllers\AgencyMangerTaregetController;
use App\Admin\Controllers\AppearChargerAgencyController;
use App\Admin\Controllers\FamilyConfigSettingController;
use App\Admin\Controllers\AgencyMangerAgencyesController;
use App\Admin\Controllers\NotificationsTemplatesController;
use Modules\Public\Http\Controllers\web\UpgradeLevelController;

Route::group(
    [
        'prefix' => config('admin.route.prefix'),
        'namespace' => '',
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
        ],
        'as' => config('admin.route.prefix') . '.',
    ],
    function (Router $router) {
        Route::post('login', App\Admin\Controllers\AuthController::class . '@postLogin');
    }
);

Route::group(
    [
        'prefix' => config('admin.route.prefix'),
        'namespace' => config('admin.route.namespace'),
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
            'production.error'
        ],
        'as' => config('admin.route.prefix') . '.',
    ],
    function (Router $router) {
        $router->get('helpers/terminal/database', [TerminalController::class, 'database']);
        $router->post('helpers/terminal/database',   [TerminalController::class, 'runDatabase']);
        $router->get('helpers/terminal/artisan',  [TerminalController::class, 'artisan']);
        $router->post('helpers/terminal/artisan', [TerminalController::class, 'runArtisan']);
        $router->get('helpers/scaffold',  [ScaffoldController::class, 'index']);
        $router->post('helpers/scaffold', [ScaffoldController::class, 'store']);
        $router->get('helpers/routes', [RouteController::class, 'index']);
    }
);

Admin::routes();
Route::group(
    [
        'prefix' => config('admin.route.prefix'),
        'namespace' => config('admin.route.namespace'),
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
        ],
        'as' => config('admin.route.prefix') . '.',
    ],
    function (Router $router) {
        Route::post('targe-percentage', [AddTargetToJsonController::class, 'targetPercentage'])->name('target-percentage');


        $router->post('ovip-config', [UpgradeLevelController::class, 'ovipConfig'])->name('ovip-config');
        $router->post('group-chat-config', [UpgradeLevelController::class, 'group_chat_config'])->name('group-chat-config');
        $router->post('reel-config', [UpgradeLevelController::class, 'reelConfig'])->name('reel-config');
        $router->post('moment-config', [UpgradeLevelController::class, 'momentConfig'])->name('moment-config');

        Route::post('/locale', MultiLanguageController::class . '@locale');
        if (MultiLanguage::config("show-login-page", true)) {
            Route::get('login', MultiLanguageController::class . '@getLogin');
        }
        $router->resource('questions', QuestionController::class);
        $router->resource('user-online-history', UserOnlineHistoryController::class);
        $router->post('create-preview-user', [App\Admin\Controllers\AuthController::class, "createPreviewUser"]);

        $router->resource('rooms-preview', TestController::class); //



        $router->get('agency-user-job/{agency_id}', 'AgencyUserJobController@index');
        $router->get('agency-user-job/{agency_id}/create', 'AgencyUserJobController@create');
        $router->get('agency-user-job/{agency_id}', 'AgencyUserJobController@index');
        $router->post('agency-user-job/{agency_id}', 'AgencyUserJobController@store');
        $router->get('agency-user-job/{agency_id}/{id}/edit', 'AgencyUserJobController@edit');
        $router->get('agency-statistic', 'AgencyStatisticController@index');
        $router->get('agency-settings', 'AgencySettingController@index');

        $router->resource('test-test', 'TestTestController');
        $router->get('profile', [AdminAuthController::class, 'index']);
        $router->resource('payment-with-method', PaymentMethodController::class);
        $router->post('save-payment-with-method', [PaymentMethodController::class, "customStore"]);

        $router->resource('auth/users', 'AdminUserController')->names([
            'index' => 'auth.users.index',
            'create' => 'auth.users.create',
            'store' => 'auth.users.store',
            'show' => 'auth.users.show',
            'edit' => 'auth.users.edit',
            'update' => 'auth.users.update',
            'destroy' => 'auth.users.destroy',
        ]);
        $router->resource('/agencies/managers', AdminAgencyMangerController::class);
        $router->resource('auth/roles', 'RoleController');
        $router->resource('auth/permissions', PermissionController::class);
        $router->resource('colors', ColorController::class);
        $router->post('app-setting', [ColorController::class, 'appSetting'])->name("app-setting");
        $router->resource('app-features', AppFeatureController::class);
        //resources
        $router->resource('users', 'UserController', [
            'names' => [
                'index' => 'users',
                'show' => 'users.show'
            ]
        ]);

        $router->resource('free-users', 'FreeUserController');

        $router->resource('family-users', 'UserFamilyController');
        $router->post('send-request-invite-code', 'UserController@request_invite_code');
        $router->resource('user-statistics', 'UserStatisticsController');
        $router->resource('profiles', 'ProfileController');
        $router->resource('vips', 'VipController');
        $router->get('vips-sender', [VipController::class, 'senderIndex']);
        $router->get('vips-receiver', [VipController::class, 'receiverIndex']);
        $router->get('vips-cp', [VipController::class, 'cpIndex']);
        $router->get('vips-room', [VipController::class, 'roomIndex']);
        $router->get('vips-charge', [VipController::class, 'chargeIndex']);
        $router->resource('rooms', 'RoomController', [
            'names' => [
                'index' => 'rooms'
            ]
        ]);
        $router->put('rooms/{id}/update-pin-status', [RoomController::class, 'updatePinStatus']);
        $router->resource('all-games', AllGameController::class);
        $router->resource('game-charge-histories', GameChargeHistoryController::class)->middleware(['auth.redirect', 'clear.session']);
        $router->resource('blacks', 'BlackListController');
        Route::prefix('black-lists')->group(function () {
            Route::get('/', [BlackListUsersController::class, 'index']);
        });
        $router->resource('codes', 'CodeController');
        $router->resource('gifts', 'GiftController', [
            'names' => [
                'index' => 'gifts'
            ]
        ]);
        $router->resource('charge-vips', ChargeVipController::class);
        $router->resource('delete-accounts', DeleteAccountController::class);
        $router->resource('wares', 'WareController', ['names' => ['index' => 'wares']]);
        $router->resource('test-pusher', TestPusherController::class);
        $router->resource('report_user', ReportUserController::class);
        $router->resource('coupons', 'CouponController');
        $router->resource('configs', 'ConfigController');
        $router->resource('categories', 'RoomCategoryController');
        $router->resource('countries', 'CountryController');
        $router->resource('backgrounds', 'BackgroundController');
        $router->resource('official_msgs', 'OfficialMessageController');
        $router->resource('emojis', 'EmojiController');
        $router->resource('home_carousels', 'HomeCarouselController');
        $router->resource('vip_prev', 'VipAuthController');
        $router->resource('agencies', 'AgencyController');
        $router->get('agencies/profile/{id}', [AgencyController::class, 'profile'])->name('agency.profile');
        $router->resource('families', 'FamilyController');
        $router->resource('targets', 'TargetController');
        Route::get('/download-target-pdf', [TargetController::class, 'downloadTargetPdf'])->name('download.target.pdf');
        $router->resource('polices', PoliceController::class);
        $router->resource('offers', OfferController::class);
        $router->resource('payment-gateways', PaymentGetWayController::class);
        $router->resource('payment-coins', PaymentCoinController::class);
        $router->resource('charges', 'ChargeController', [

            'names' => [
                'index' => 'charges',
                'show' => 'charges.show'
            ]
        ]);
        $router->resource('charges-details', 'ChargesDetailsController', [

            'names' => [
                'index' => 'charges-details',
                'show' => 'charges-details.show'
            ]
        ]);
        $router->resource('commissions', 'CommissionController', [

            'names' => [
                'index' => 'commissions',
                'show' => 'commission.show'
            ]
        ]);
        $router->resource('charge_values', 'ChargeValueController');
        $router->resource('userTarget', 'UserTargetController', [
            'names' => [
                'index' => 'user_targets'
            ]
        ]);
        // $router->get('/', 'HomeController@infoBox')->name('home');
        $router->get('/', 'AllStatisticController@index')->name('home');

        $router->get('/soon', 'AllStatisticController@index2');
        $router->get('app-earned', 'AppEarnedController@index')->name('app-earned');
        $router->get('/custom-export-users', [
            ExportController::class,
            'usersSallaryTargets'
        ])->name('custom-export-users');
        $router->get('/agency-export-report', [
            ExportController::class,
            'usersAgencyTargets'
        ])->name('agency-export-report');
        $router->get('/dev', 'HomeController@devindex')->name('dev-home');
        //        $router->get('/agency_home', 'HomeController@agencyInfoBox')->name('agency.home');
        $router->resource('wares-vips', WareVipController::class);
        // servers
        $router->resource('server-country', ServerCountryController::class);
        $router->resource('room-gift-targets', RoomGiftTargetController::class);

        //--------------------
        // $router->get('/', 'HomeController@infoBox')->name('home');
        $router->get('/dev', 'HomeController@devindex')->name('dev-home');
        $router->get('/agency_home', 'HomeController@agencyInfoBox')->name('agency2.home');
        $router->resource('manger-types', 'MangerTypeController');
        $router->resource('chat-letters', ChatLetterController::class);
        $router->resource('userscharg', chargUsersSleemController::class);
        $router->resource('image-colors', ImageColorController::class);
        $router->resource('agency_join_requests', 'AgencyJoinRequestController');
        $router->resource('requests-for-get-salary', 'GetSalaryRequestController');
        $router->resource('requests-for-get-salary-history', 'GetSalaryRequestFilterationController');
        $router->resource('special-id-requests', 'SpecialIdRequestController');
        $router->resource('family_levels', 'FamilyLevelController');
        $router->resource('silver', 'SilverController');

        // $router->resource('coins/{paymentGatwayId}', 'CoinController')->only(['create', 'store', 'destroy']);
        // $router->get('coins/{paymentGatwayId}/{id}/edit', 'CoinController@edit');
        // $router->put('coins/{paymentGatwayId}/{id}', 'CoinController@update');

        Route::prefix('coins/{paymentGatwayId}')->group(function () {
            Route::get('/', [CoinController::class, 'index'])->name('coins.index');
            Route::get('/create', [CoinController::class, 'create'])->name('coins.create');
            Route::post('/', [CoinController::class, 'store'])->name('coins.store');
            Route::get('/{id}', [CoinController::class, 'show'])->name('coins.show');
            Route::get('/{id}/edit', [CoinController::class, 'edit'])->name('coins.edit');
            Route::put('/{id}', [CoinController::class, 'update'])->name('coins.update');
            Route::delete('/{id}', [CoinController::class, 'destroy'])->name('coins.destroy');
        });



        $router->resource('ovip', 'OVipController');
        $router->get('ovip-settings', [OVipController::class, 'vip_settings']);


        Route::get('ovip-gift/{ovip_id}/{type?}', [OvipGiftTapController::class, 'index']);

            Route::get('room-mic/{room_id}/', [RoomMicController::class, 'index']);

            Route::prefix('ware-gift')->group(function () {
                Route::get('/{level}/{type}', [OvipGiftTapController::class, 'create']);
                Route::post('/{level}', [OvipGiftTapController::class, 'store']);
                // Route::get('/{id}/edit', [OvipGiftTapController::class, 'edit'])->where('id', '[0-9]+');
                // Route::put('/{id}', [OvipGiftTapController::class, 'update'])->where('id', '[0-9]+');
                // Route::delete('/{id}', [OvipGiftTapController::class, 'destroy'])->where('id', '[0-9]+');
            });

            $router->resource('ware-gifts', 'OvipGiftTapController');
            Route::prefix('ware-gifts')->group(function () {


                Route::get('/{id}/edit', [OvipGiftTapController::class, 'edit'])->where('id', '[0-9]+');
                Route::put('/{id}', [OvipGiftTapController::class, 'update'])->where('id', '[0-9]+');
                Route::delete('/{id}', [OvipGiftTapController::class, 'destroy'])->where('id', '[0-9]+');
            });
        $router->resource('vip_privilege', 'VipPrivilegeController');
        $router->resource('tickets', 'TicketController');
        $router->resource('pages', 'PageController');
        $router->resource('exchanges', 'ExchangeController');
        $router->resource('boxes', 'BoxController');
        $router->get('lucy-box-settings', [BoxController::class, 'box_settings']);
        $router->resource('thrown_boxes', 'BoxUseController');
        $router->resource('reports', 'ReportController');
        $router->resource('charges-reports', 'ChargeReportController');
        $router->get('charge-reports/{agency_id}', [ChargeReportController::class, 'showChargeReports']);
        $router->resource('sallaries', 'SallariesController')->name('index', 'sallaries');
        $router->resource('total-statistics', 'AllStatisticController');
        $router->resource('coin-reports', 'CoinReportController');
        $router->resource('ban-types', BanTypeController::class);
        $router->resource('sallaries_history', 'SallariesHistoryController');
        // $router->resource ('export-excel','ImportExcelReportController');
        $router->resource('agencies-tareget-manger', AgencyMangerTaregetController::class);
        $router->resource('report_users', ReportFromUsersController::class);
        $router->post('cashing', 'ReportController@cashing')->name('cashing');
        $router->resource('trxs', 'CoinLogController');
        $router->resource('images', 'ImageController');
        $router->resource('moments', MomentController::class);
        $router->get('moment-gallery/{id}', [MomentController::class,'momentGallery']);
        $router->resource('moment-settings', MomentSettingsController::class);
        $router->resource('reels', ReelController::class);
        $router->resource('reel-settings', ReelSettingsController::class);
        $router->resource('change-level-histories', ChangeLevelHistoryController::class);
        $router->resource('levels/users', UserLevelController::class)->names([
            'index' => 'levels.users.index',
            'create' => 'levels.users.create',
            'store' => 'levels.users.store',
            'show' => 'levels.users.show',
            'edit' => 'levels.users.edit',
            'update' => 'levels.users.update',
            'destroy' => 'levels.users.destroy',
        ]);
        $router->resource('trashed-users', TrashedUserAccountController::class);
        $router->resource('withdraw-types', WithdrawController::class);
        $router->resource('room-vips', RoomVipController::class);
        $router->resource('room-target', RoomTargetController::class);

        // $router->resource('agencyMangLink', AgencyMangerLinkController::class);


        Route::prefix('ag')->name('agency.')->namespace('AgencyControllers')->group(function (Router $router) {
            $router->get('/', 'HomeController@infoBox')->name('home');
            $router->get('/users', 'UserController@index')->name('users');
            $router->get('/users/{id}/edit', 'UserController@edit');
            $router->get('/users/{id}', 'UserController@show');
            $router->get('/userTarget', 'UserTargetController@index')->name('userTarget');
            $router->get('/target', 'AgencyTargetController@index')->name('targets');
            $router->get('/charges', 'ChargeController@index')->name('charges');
            $router->resource('/ag-req', 'AgencyJoinRequestController');
        });

        Route::prefix('ch')->name('charger.')->namespace('ChargerControllers')->group(function (Router $router) {
            $router->get('/', 'HomeController@infoBox')->name('home');
            $router->get('/charges', 'ChargeController@index')->name('charges');
        });

        $router->resource('/wares_dedicate', 'DedicateWareController')->only('index', 'create', 'store');
        $router->resource('/uuid_dedicate', 'SpecialWareDedicateController');
        $router->get('/vips_dedicate', 'DedicateVipController@index');
        $router->resource('/bans', 'BanController');
        $router->resource('/bans-rooms', 'BanRoomsController');

        $router->resource('/request-background-image', 'RequestBackgroundImageController');
        $router->resource('/group-chat', 'GroupChatController');
        $router->resource('interests', InterestsController::class);
        $router->resource('custom-settings', CustomController::class);
        $router->get('/custom-page', [AppSitiingCOnfigController::class, 'index'])->name('admin.AppSitiingCOnfigController');
        $router->get('/setting-group-char', [GroupChatSettingController::class, 'index']);
        $router->get('/setting-family', [FamilyConfigSettingController::class, 'index']);
        $router->get('/agency-setting-manger', [MangerSettingController::class, 'index']);
        $router->resource('agencies-agency-manger', AgencyMangerAgencyesController::class);
        $router->resource('agency-manger-users', AgencyMangerUsers::class);
        $router->resource('core-wallets', CoreWalletsController::class);
        $router->resource('change_agencies_manger', ChangeAgencyMangerController::class);
        $router->resource('charge-agencies', AppearChargerAgencyController::class);

        //    dd( Admin::menu(function ($menu) {
        //         $menu->add('Custom Page', ['route' => 'admin.AppSitiingCOnfigController'])
        //             ->icon('fa-file');
        //     }));

        // $router->get('/custom-page', [AppSitiingCOnfigController::class, 'index'])->name('admin.AppSitiingCOnfigController');
        $router->resource('report-reals', ReportRealsController::class);
        $router->resource('report-moments', ReportMomentController::class);
        $router->resource('admin-users', AdminUsersController::class);
        $router->resource('parent-users', ParentUsersController::class);
        $router->resource('custom-zego-messages', CustomZegoMessageController::class);
        $router->resource('agency-settings', AgencySettingsController::class);
        $router->get('chat-settings', [GroupChatController::class, 'chat_settings']);
        $router->get('admin-users/{id}/{agency}', 'AdminUsersController@show2');
        //$router->get('percentage-target', [TargetPercentageController::class, 'index'])->name('percentage-target');
        $router->get('convert-is_gold', function () {
            $users = \App\Models\User::where("is_gold_id", 1)->get();
            foreach ($users as $user) {
                $user->image_color_id = 1;
                $user->save();
            }
            dD("goold");
        });
        $router->get('background-count', function () {
            $backgrounds = \App\Models\Background::get();
            if ($backgrounds) {
                foreach ($backgrounds as $background) {
                    $background_count = \App\Models\Room::where("room_background", $background->id)->count();
                    $background->use_count = $background_count;
                    $background->save();
                }
                //dd("done");
            }
            //dd("note found data");
        });


        $router->resource('banners', BannerController::class);
        $router->resource('languages', LanguageController::class);
        $router->resource('settings', SettingController::class);
        $router->resource('room-settings', RoomSettingsController::class);
        $router->resource('charges-settings', ChargesSettingController::class);
        Route::post('save_image', [SettingController::class, 'save_image'])->name('save_image');


        Route::post('rooms/{room}/pin', function (Room $room) {
            $room->update(['pin' => !$room->pin]);

            return response()->json(['success' => true, 'message' => 'Pin updated successfully']);
        })->name('rooms.pin');


        $router->resource('notification-templates', NotificationsTemplatesController::class);
       // Route::get('ware-management', [WareTabController::class, 'index']);
      //  $router->resource('ware-management', WareTabController::class);
      //Route::post('/ware-management/create/{type?}', [WareTabController::class, 'create']);
      Route::get('/ware-managements/create/{type}', [WareTabController::class, 'create']);
      Route::post('/ware-managements/create', [WareTabController::class, 'store']);
        Route::prefix('ware-management')->group(function () {
            Route::get('/{type?}', [WareTabController::class, 'index']);
            Route::get('/{id}/edit', [WareTabController::class, 'edit'])->where('id', '[0-9]+');
            Route::put('/{id}', [WareTabController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [WareTabController::class, 'destroy'])->where('id', '[0-9]+');
        });

    }





);

