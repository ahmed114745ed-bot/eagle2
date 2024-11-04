<?php

use Illuminate\Routing\Router;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Route;
use App\Admin\Controllers\ReelController;
use App\Admin\Controllers\ColorController;
use App\Admin\Controllers\OfferController;
use App\Admin\Controllers\RouteController;
use KevinSoft\MultiLanguage\MultiLanguage;
use App\Admin\Controllers\MomentController;
use App\Admin\Controllers\PoliceController;
use App\Admin\Controllers\AgencyMangerUsers;
use App\Admin\Controllers\AllGameController;
use App\Admin\Controllers\BanTypeController;
use App\Admin\Controllers\RoomVipController;
use App\Admin\Controllers\WareVipController;
use App\Admin\Controllers\QuestionController;
use App\Admin\Controllers\ScaffoldController;
use App\Admin\Controllers\TerminalController;
use App\Admin\Controllers\WithdrawController;
use App\Admin\Controllers\UserLevelController;
use App\Admin\Controllers\AdminUsersController;
use App\Admin\Controllers\AppFeatureController;
use App\Admin\Controllers\ImageColorController;
use App\Admin\Controllers\PermissionController;
use App\Admin\Controllers\ReportUserController;
use App\Admin\Controllers\RoomTargetController;
use App\Admin\Controllers\TestPusherController;
use App\Admin\Controllers\CoreWalletsController;
use App\Admin\Controllers\ParentUsersController;
use App\Admin\Controllers\ReportMomentController;
use App\Admin\Controllers\MultiLanguageController;
use App\Admin\Controllers\PaymentGetWayController;
use App\Admin\Controllers\ServerCountryController;
use App\Admin\Controllers\BlackListUsersController;
use App\Admin\Controllers\RoomGiftTargetController;
use App\Admin\Controllers\chargUsersSleemController;
use App\Admin\Controllers\AppSitiingCOnfigController;
use App\Admin\Controllers\TargetPercentageController;
use App\Admin\Controllers\AdminAgencyMangerController;
use App\Admin\Controllers\CustomZegoMessageController;
use App\Admin\Controllers\GameChargeHistoryController;
use App\Admin\Controllers\UserOnlineHistoryController;
use App\Admin\Controllers\ChangeAgencyMangerController;
use App\Admin\Controllers\TrashedUserAccountController;
use App\Admin\Controllers\AgencyMangerTaregetController;
use App\Admin\Controllers\AppearChargerAgencyController;
use App\Admin\Controllers\AgencyMangerAgencyesController;




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
        Route::post('/locale', MultiLanguageController::class . '@locale');
        if (MultiLanguage::config("show-login-page", true)) {
            Route::get('login', MultiLanguageController::class . '@getLogin');
        }
        $router->resource('questions', QuestionController::class);
        $router->resource('user-online-history', UserOnlineHistoryController::class);
        $router->post('create-preview-user', [App\Admin\Controllers\AuthController::class, "createPreviewUser"]);

        $router->resource('rooms-preview', TestController::class);//
        $router->resource('days', DayController::class);
        $router->resource('daily-tasks', DailyTaskController::class);
        $router->resource('user-day-progresses', UserDayProgressController::class);
        $router->resource('user-day-task-progresses', UserDayTaskProgressController::class);
        $router->resource('task-rewards', TaskRewardController::class);
        $router->resource('user-task-rewards', UserTaskRewardController::class);
        

        $router->get('agency-user-job/{agency_id}', 'AgencyUserJobController@index');
        $router->get('agency-user-job/{agency_id}/create', 'AgencyUserJobController@create');
        $router->get('agency-user-job/{agency_id}', 'AgencyUserJobController@index');
        $router->post('agency-user-job/{agency_id}', 'AgencyUserJobController@store');
        $router->get('agency-user-job/{agency_id}/{id}/edit', 'AgencyUserJobController@edit');
        $router->get('agency-statistic', 'AgencyStatisticController@index');
        $router->get('agency-settings', 'AgencySettingController@index');
        $router->resource('test-test', 'TestTestController');

        $router->resource('auth/users', 'AdminUserController');
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
        $router->post('send-request-invite-code', 'UserController@request_invite_code');
        $router->resource('user-statistics', 'UserStatisticsController');
        $router->resource('profiles', 'ProfileController');
        $router->resource('vips', 'VipController');
        $router->resource('rooms', 'RoomController', [
            'names' => [
                'index' => 'rooms'
            ]
        ]);
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
        $router->resource('families', 'FamilyController');
        $router->resource('targets', 'TargetController');
        $router->resource('polices', PoliceController::class);
        $router->resource('offers', OfferController::class);
        $router->resource('payment-gateways', PaymentGetWayController::class);
        $router->resource('charges', 'ChargeController', [

            'names' => [
                'index' => 'charges',
                'show' => 'charges.show'
            ]
        ]);
        $router->resource('charges-details', 'ChargesDetailsController', [

            'names' => [
                'index' => 'charges-details',
                'show' => 'charges.show'
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
        $router->get('app-earned', 'AppEarnedController@index')->name('app-earned');
        $router->get('/custom-export-users', [
            \App\Admin\Controllers\ExportController::class,
            'usersSallaryTargets'
        ])->name('custom-export-users');
        $router->get('/agency-export-report', [
            \App\Admin\Controllers\ExportController::class,
            'usersAgencyTargets'
        ])->name('agency-export-report');
        $router->get('/dev', 'HomeController@devindex')->name('dev-home');
        $router->get('/agency_home', 'HomeController@agencyInfoBox')->name('agency.home');
        $router->resource('wares-vips', WareVipController::class);
        // servers
        $router->resource('server-country', ServerCountryController::class);
        $router->resource('room-gift-targets', RoomGiftTargetController::class);

        //--------------------
        // $router->get('/', 'HomeController@infoBox')->name('home');
        $router->get('/dev', 'HomeController@devindex')->name('dev-home');
        $router->get('/agency_home', 'HomeController@agencyInfoBox')->name('agency.home');
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
        $router->resource('coins', 'CoinController');
        $router->resource('ovip', 'OVipController');
        $router->resource('vip_privilege', 'VipPrivilegeController');
        $router->resource('tickets', 'TicketController');
        $router->resource('pages', 'PageController');
        $router->resource('exchanges', 'ExchangeController');
        $router->resource('boxes', 'BoxController');
        $router->resource('thrown_boxes', 'BoxUseController');
        $router->resource('reports', 'ReportController');
        $router->resource('charges-reports', 'ChargeReportController');
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
        $router->resource('reels', ReelController::class);
        $router->resource('levels/users', UserLevelController::class);
        $router->resource('trashed-users', TrashedUserAccountController::class);
        $router->resource('withdraw-types', WithdrawController::class);
        $router->resource('room-vips', RoomVipController::class);
        $router->resource('room-target', RoomTargetController::class);
   
        // $router->resource('agencyMangLink', AgencyMangerLinkController::class);

        Route::prefix('ag')->name('agency.')->namespace('AgencyControllers')->group(function (Router $router) {
            $router->get('/', 'HomeController@infoBox')->name('home');
            $router->get('/users', 'UserController@index')->name('users');
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
        $router->get('/vips_dedicate', 'DedicateVipController@index');
        $router->resource('/bans', 'BanController');
        $router->resource('/request-background-image', 'RequestBackgroundImageController');
        $router->resource('/group-chat', 'GroupChatController');
        $router->resource('interests', InterestsController::class);
        $router->get('/custom-page', [AppSitiingCOnfigController::class, 'index'])->name('admin.AppSitiingCOnfigController');
        $router->resource('agencies-agency-manger', AgencyMangerAgencyesController::class);
        $router->resource('agency-manger-users', AgencyMangerUsers::class);
        $router->resource('core-wallets', CoreWalletsController::class);
        $router->resource('change_agencies_manger', ChangeAgencyMangerController::class);
        $router->resource('appear-charger-agency', AppearChargerAgencyController::class);

        //    dd( Admin::menu(function ($menu) {
        //         $menu->add('Custom Page', ['route' => 'admin.AppSitiingCOnfigController'])
        //             ->icon('fa-file');
        //     }));

        $router->get('/custom-page', [AppSitiingCOnfigController::class, 'index'])->name('admin.AppSitiingCOnfigController');
        $router->resource('report-reals', ReportRealsController::class);
        $router->resource('report-moments', ReportMomentController::class);
        $router->resource('admin-users', AdminUsersController::class);
        $router->resource('parent-users', ParentUsersController::class);
        $router->resource('custom-zego-messages', CustomZegoMessageController::class);
        $router->get('admin-users/{id}/{agency}', 'AdminUsersController@show2');
        $router->get('percentage-target', [TargetPercentageController::class, 'index'])->name('percentage-target');
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
                dd("done");
            }
            dd("note found data");
        });


        $router->resource('banners', BannerController::class);
    }

    
);
