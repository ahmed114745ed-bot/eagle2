<?php

use App\Admin\Controllers\AdminAgencyMangerController;
use Utd\AreaManager\Admin\Controllers\AdminAreaManagerChargeController;
use App\Admin\Controllers\AdminAuthController;
use App\Admin\Controllers\AdminUsersController;
use App\Admin\Controllers\AgencyController;
use App\Admin\Controllers\AgencyControllers\HostDiamondController;
use App\Admin\Controllers\AgencyControllers\UserController;
use App\Admin\Controllers\AgencyMangerAgencyesController;
use App\Admin\Controllers\AgencyMangerTaregetController;
use App\Admin\Controllers\AgencyMangerUsers;
use App\Admin\Controllers\AgencySettingsController;
use App\Admin\Controllers\AllGameController;
use App\Admin\Controllers\AllStatisticController;
use App\Admin\Controllers\AppFeatureController;
use App\Admin\Controllers\AppSitiingCOnfigController;
use App\Admin\Controllers\BanController;
use App\Admin\Controllers\BannerController;
use App\Admin\Controllers\BanTypeController;
use App\Admin\Controllers\BlackListUsersController;
use App\Admin\Controllers\ChangeAgencyMangerController;
use App\Admin\Controllers\ChangeCountryRequestController;
use App\Admin\Controllers\ChangeLevelHistoryController;
use App\Admin\Controllers\ChargeReportController;
use App\Admin\Controllers\ChargesSettingController;
use App\Admin\Controllers\ChargeVipController;
use App\Admin\Controllers\chargUsersSleemController;
use App\Admin\Controllers\CoinController;
use App\Admin\Controllers\CoinGameUserAllController;
use App\Admin\Controllers\CoinLogReportsController;
use App\Admin\Controllers\ColorController;
use App\Admin\Controllers\CoreWalletsController;
use App\Admin\Controllers\CoreWalletTransactionController;
use App\Admin\Controllers\CountryCategoryController;
use App\Admin\Controllers\CountryRequestActionController;
use App\Admin\Controllers\CountryRequestHistoryController;
use App\Admin\Controllers\CustomController;
use App\Admin\Controllers\DeleteAccountController;
use App\Admin\Controllers\EmojiCategoryController;
use App\Admin\Controllers\EmojiController;
use App\Admin\Controllers\ExportController;
// use App\Admin\Controllers\FamilyConfigSettingController;
use App\Admin\Controllers\FeatureAppController;
use App\Admin\Controllers\GameChargeHistoryController;
use App\Admin\Controllers\GameSettingsController;
use App\Admin\Controllers\GiftLogSummaryController;
use App\Admin\Controllers\GiftLogTestController;
use App\Admin\Controllers\HomeCarouselController;
use App\Admin\Controllers\ImageColorController;
use App\Admin\Controllers\InterestsController;
use App\Admin\Controllers\InvitationSettingsController;
use App\Admin\Controllers\LanguageController;
use App\Admin\Controllers\LinkViewController;
use App\Admin\Controllers\MultiLanguageController;
use App\Admin\Controllers\NotificationController;
use App\Admin\Controllers\NotificationsTemplatesController;
use App\Admin\Controllers\OfferController;
use App\Admin\Controllers\ParentUsersController;
use App\Admin\Controllers\PaymentCoinController;
use App\Admin\Controllers\PaymentMethodController;
use App\Admin\Controllers\PermissionController;
use App\Admin\Controllers\PoliceController;
use App\Admin\Controllers\PusherStatisticsController;
use App\Admin\Controllers\QuestionController;
use App\Admin\Controllers\RemainingDiamondHistoryController;
use App\Admin\Controllers\RemainingDiamondSettingController;
use App\Admin\Controllers\ReportController;
use App\Admin\Controllers\ReportFromUsersController;
use App\Admin\Controllers\ReportUserController;
use App\Admin\Controllers\ResetUserSalaryController;
use App\Admin\Controllers\RoleControllerNew;
use App\Admin\Controllers\UserHistoryRewardController;
use App\Admin\Controllers\RouteController;
use App\Admin\Controllers\ScaffoldController;
use App\Admin\Controllers\ServerCountryController;
use App\Admin\Controllers\SettingController;
use App\Admin\Controllers\ShippingAgencyPaymentCoinController;
use App\Admin\Controllers\SuperAdminRewardController;
use App\Admin\Controllers\SuperAdminRewardControllerHistory;
use App\Admin\Controllers\SuperPackageController;
use App\Admin\Controllers\SuperRoleController;
use App\Admin\Controllers\TargetController;
use App\Admin\Controllers\TerminalController;
use App\Admin\Controllers\TestPusherController;
use App\Admin\Controllers\TrashedUserAccountController;
use App\Admin\Controllers\UserChargeHistoryController;
use App\Admin\Controllers\UserChargeReportController;
use App\Admin\Controllers\UserController as UsersAppController;
use App\Admin\Controllers\UserLevelController;
use App\Admin\Controllers\UserOnlineHistoryController;
use App\Admin\Controllers\UsersChargeController;
use App\Admin\Controllers\UserSettingController;
use App\Admin\Controllers\UsersJoinedAgencyController;
use App\Admin\Controllers\UserWalletController;
use App\Admin\Controllers\V2\SalariesController;
use App\Admin\Controllers\WalletTransactionController;
use App\Admin\Controllers\WareController;
use App\Admin\Controllers\WareTabController;
use App\Admin\Controllers\WithdrawController;
use App\Admin\Controllers\ZegoFeatureController;
use App\Http\Controllers\AddTargetToJsonController;
use App\Http\Controllers\Api\V1\UserController as UserV1Controller;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Route;
use KevinSoft\MultiLanguage\MultiLanguage;
use Modules\Public\Http\Controllers\web\UpgradeLevelController;
use Modules\SuperAdmin\Http\Controllers\Admin\SuperAdminController;
use Utd\Agency\Http\Controllers\Admin\PackageController;

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
        Route::post('login', App\Admin\Controllers\AuthController::class . '@postLogin');
    }
);

Route::group([
    'prefix' => config('admin.route.prefix'),
    'middleware' => [
        'web',
        'adminIp',
        'multiLanguage',
    ],
], function () {

    Route::get('change-password-view', [App\Admin\Controllers\AuthController::class, 'changePasswordView'])
        ->name('admin.change-password-view');
});

Route::group(
    [
        'prefix' => config('admin.route.prefix'),
        'namespace' => config('admin.route.namespace'),
        'middleware' => [
            'web',
            'admin.auth',
            'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
            'production.error'
        ],
        'as' => config('admin.route.prefix') . '.',
    ],
    function () {
        Route::get('helpers/terminal/database', [TerminalController::class, 'database']);
        Route::post('helpers/terminal/database', [TerminalController::class, 'runDatabase']);
        Route::get('helpers/terminal/artisan', [TerminalController::class, 'artisan']);
        Route::post('helpers/terminal/artisan', [TerminalController::class, 'runArtisan']);
        Route::get('helpers/scaffold', [ScaffoldController::class, 'index']);
        Route::post('helpers/scaffold', [ScaffoldController::class, 'store']);
        Route::get('helpers/routes', [RouteController::class, 'index']);
    }
);

Admin::routes();

$routes = collect(app('router')->getRoutes()->get());
$filtered = $routes->reject(function ($route) {
    return str_starts_with($route->getName() ?? '', 'admin.auth.roles.');
});



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
    function () {

//         Route::get('agency/install', [PackageController::class, 'install'])->name('agency.install');
        // Route::get('agency/uninstall', [PackageController::class, 'uninstall'])->name('agency.uninstall');

        Route::post('targe-percentage', [AddTargetToJsonController::class, 'targetPercentage'])->name('target-percentage');

        Route::resource('reset-salary', ResetUserSalaryController::class);
        Route::get('user-charge-history/{User_id}', [UserChargeHistoryController::class, 'indexCharge']);
        Route::post('ovip-config', [UpgradeLevelController::class, 'ovipConfig'])->name('ovip-config');
        Route::post('exchange-coins', [UpgradeLevelController::class, 'exchange'])->name('exchange-coins');
        Route::post('group-chat-config', [UpgradeLevelController::class, 'group_chat_config'])->name('group-chat-config');

        Route::get('search/super-admin', [SuperAdminController::class, 'searchBySuperAdmin'])->name('super-admin');

        Route::get('search/host-agency', [UserV1Controller::class, 'hostAgencies'])->name('hostAgency');
        Route::post('/locale', MultiLanguageController::class . '@locale');
        if (MultiLanguage::config("show-login-page", true)) {
            Route::get('login', MultiLanguageController::class . '@getLogin');
        }
        Route::resource('questions', QuestionController::class);
        Route::resource('user-online-history', UserOnlineHistoryController::class);
        Route::post('create-preview-user', [App\Admin\Controllers\AuthController::class, "createPreviewUser"]);

        Route::resource('rooms-preview', TestController::class); //

        // Agency routes - only load if package is installed
        if (\App\Helpers\AgencyPackageHelper::isAgencyInstalled()) {
            Route::get('agency-user-job/{agency_id}', 'AgencyUserJobController@index');
            Route::get('agency-user-job/{agency_id}/create', 'AgencyUserJobController@create');
            Route::get('agency-user-job/{agency_id}', 'AgencyUserJobController@index');
            Route::post('agency-user-job/{agency_id}', 'AgencyUserJobController@store');
            Route::get('agency-user-job/{agency_id}/{id}/edit', 'AgencyUserJobController@edit');
            Route::get('agency-statistic', 'AgencyStatisticController@index');
            //    Route::get('agency-settings', 'AgencySettingController@index');
        }

        Route::resource('test-test', 'TestTestController');
        Route::get('profile', [AdminAuthController::class, 'index']);
        Route::resource('payment-with-method', PaymentMethodController::class);
        Route::post('save-payment-with-method', [PaymentMethodController::class, "customStore"]);
        Route::resource('users-settings', UserSettingController::class);


        Route::resource('auth/users', 'AdminUserController')->names([
            'index' => 'auth.users.index',
            'create' => 'auth.users.create',
            'store' => 'auth.users.store',
            'show' => 'auth.users.show',
            'edit' => 'auth.users.edit',
            'update' => 'auth.users.update',
            'destroy' => 'auth.users.destroy',
        ]);


        Route::resource('auth/roles', RoleControllerNew::class);
        Route::resource('user-history-rewards', UserHistoryRewardController::class);
        Route::get('super-roles', [SuperRoleController::class, 'index']);
        Route::post('update-super-roles', [SuperRoleController::class, 'updatePermissionRole'])->name('admin.update-super-roles');


        Route::resource('auth/rolesTest', 'RoleController');

        Route::get('/permissions/category/{category}', [RoleControllerNew::class, 'getPermissionsByCategory']);

        Route::resource('auth/permissions', PermissionController::class);
        Route::resource('colors', ColorController::class);
        Route::post('app-setting', [ColorController::class, 'appSetting'])->name("app-setting");
        Route::resource('app-features', AppFeatureController::class);
        //resources
        Route::resource('users', 'UserController', [
            'names' => [
                'index' => 'users',
                'show' => 'users.show'
            ]
        ]);
        Route::get('users/{id}/same-device-users-table', [UsersAppController::class, 'ajaxSameDeviceUsersTable']);
        Route::post('delete-badge/{id}', [UsersAppController::class, 'deleteBadge']);

        Route::post('/update-user', [UsersAppController::class, 'updateUsers']);

        Route::post('/edit-level', [UsersAppController::class, 'editLevelUser']);
        Route::post('/delete-pack/{id}', [UsersAppController::class, 'deletePack']);
        Route::post('/delete-user-vip/{id}', [UsersAppController::class, 'deleteUserVip']);
        Route::post('/pack/free', [UsersAppController::class, 'free'])->name('pack.free');

        //        Route::get('users/profile/{id}', [UsersAppController::class, 'profile'])->name('user.profile');

        Route::resource('free-users', 'FreeUserController');
        Route::post('home-carousel-display-toggle', [HomeCarouselController::class, 'toggleStatus']);

        Route::post('send-request-invite-code', 'UserController@request_invite_code');
        Route::resource('user-statistics', 'UserStatisticsController');
        Route::resource('profiles', 'ProfileController');
        Route::resource('all-games', AllGameController::class);
        Route::resource('game-settings', GameSettingsController::class);
        Route::resource('game-charge-histories', GameChargeHistoryController::class)->middleware(['auth.redirect', 'clear.session']);
        Route::resource('blacks', 'BlackListController');
        Route::prefix('black-lists')->group(function () {
            Route::get('/', [BlackListUsersController::class, 'index']);
        });
        Route::resource('codes', 'CodeController');

        Route::get('home-carousel-settings', [HomeCarouselController::class, 'homeCarouselSettings']);
        Route::resource('charge-vips', ChargeVipController::class);
        Route::resource('delete-accounts', DeleteAccountController::class);
        Route::resource('wares', 'WareController', ['names' => ['index' => 'wares']]);
        Route::put('wares/toggle-enable/{id}', [WareController::class, 'toggleEnable']);

        Route::resource('test-pusher', TestPusherController::class);
        Route::resource('report_user', ReportUserController::class)->middleware('web-agency-feature');
        // Route::resource('coupons', 'CouponController');
        Route::resource('configs', 'ConfigController');
        Route::resource('countries', 'CountryController')->only(['index', 'show', 'update', 'edit']);
        Route::resource('country-categories', CountryCategoryController::class);
        Route::resource('country-requests', 'ChangeCountryRequestController')->only(['index', 'show']);
        Route::get('country-request-history', [CountryRequestHistoryController::class, 'index']);
        Route::get('country-requests/{id}/accept', [ChangeCountryRequestController::class, 'accept']);
        Route::get('country-requests/{id}/reject', [ChangeCountryRequestController::class, 'reject']);
        Route::post("accept-change-country", [ChangeCountryRequestController::class, "changeCountry"]);

        Route::resource('official_msgs', 'OfficialMessageController');
        Route::resource('emojis', 'EmojiController');

        Route::get('/emojis/create/{filter}', [EmojiController::class, 'create']);
        Route::post('/emojis/create', [EmojiController::class, 'store']);

        Route::prefix('emojis')->group(function () {
            Route::get('/{filter?}', [EmojiController::class, 'index']);
        });
        Route::resource('emoji-categories', EmojiCategoryController::class);
        Route::resource('home_carousels', 'HomeCarouselController');

        // Target routes - only if agency installed
        if (\App\Helpers\AgencyPackageHelper::isAgencyInstalled()) {
            Route::resource('targets', 'TargetController')->middleware('web-agency-feature');
            Route::get('/targets-confirm', [TargetController::class, 'confirm'])->name('targets.confirm');
            Route::get('/download-target-pdf', [TargetController::class, 'downloadTargetPdf'])->name('download.target.pdf')->middleware('web-agency-feature');
            Route::get('download-target-excel', [TargetController::class, 'downloadTargetExcel'])->middleware('web-agency-feature');
        }

        Route::resource('polices', PoliceController::class);
        Route::resource('offers', OfferController::class);
        Route::resource('payment-coins', PaymentCoinController::class);

        // Shipping agency payment coins - only if shipping package installed
        if (\App\Helpers\AgencyPackageHelper::isShippingAgencyInstalled()) {
            Route::resource('shipping-agency-payment-coins', ShippingAgencyPaymentCoinController::class);
        }

        Route::resource('charges', 'ChargeController');
        Route::get('/area-manager-charges-reports', [AdminAreaManagerChargeController::class, 'index']);
        Route::resource('charges-details', 'ChargesDetailsController', [

            'names' => [
                'index' => 'charges-details',
                'show' => 'charges-details.show'
            ]
        ]);

        Route::resource('commissions', 'CommissionController', [

            'names' => [
                'index' => 'commissions',
                'show' => 'commission.show'
            ]
        ]);
        Route::resource('charge_values', 'ChargeValueController');
        Route::resource('userTarget', 'UserTargetController', [
            'names' => [
                'index' => 'user_targets'
            ]
        ])->middleware('web-agency-feature');
        // Route::get('/', 'HomeController@infoBox')->name('home');
        Route::get('/', 'AllStatisticController@index')->name('home');


        Route::get('/soon', 'AllStatisticController@index2');
        Route::get('app-earned', 'AppEarnedController@index')->name('app-earned');
        Route::get('/custom-export-users', [
            ExportController::class,
            'usersSallaryTargets'
        ])->name('custom-export-users');
        Route::get('/exchange-diamond-history', [
            ExportController::class,
            'exchangeDiamondExcel'
        ])->name('exchange-diamond-history');
        Route::get('/exchange-charge-history', [
            ExportController::class,
            'exchangeChargeExcel'
        ])->name('exchange-charge-history');
//         Route::get('/family-level', [
//             ExportController::class,
//             'familyLevelExcel'
//         ])->name('family-level');
//         Route::get('/families-excel', [
//             ExportController::class,
//             'familiesExcel'
//         ])->name('families-excel');
        Route::get('/exchange-coin-history', [
            ExportController::class,
            'exchangeCoinExcel'
        ])->name('exchange-coin-history');
        Route::get('/wallet-export-users', [
            ExportController::class,
            'walletExportUser'
        ])->name('wallet-export-users');
        Route::get('/wallet-export-agency', [
            ExportController::class,
            'walletExportAgency'
        ])->name('wallet-export-agency');
        Route::get('/agency-export-report', [
            ExportController::class,
            'usersAgencyTargets'
        ])->name('agency-export-report');
        Route::get('/agency-manger-export', [
            ExportController::class,
            'agencyMangerExport'
        ])->name('agency-manger-export');
        Route::get('/dev', 'HomeController@devindex')->name('dev-home');
        //        Route::get('/agency_home', 'HomeController@agencyInfoBox')->name('agency.home');
        // servers
        Route::resource('server-country', ServerCountryController::class);

        Route::get('/dev', 'HomeController@devindex')->name('dev-home');

        // Agency home - only if package installed
        if (\App\Helpers\AgencyPackageHelper::isAgencyInstalled()) {
            Route::get('/agency_home', 'HomeController@agencyInfoBox')->name('agency2.home');
        }

        Route::resource('manger-types', 'MangerTypeController');
        Route::resource('userscharg', chargUsersSleemController::class);
        Route::resource('image-colors', ImageColorController::class);

        // Agency join requests - only if package installed
        if (\App\Helpers\AgencyPackageHelper::isAgencyInstalled()) {
            Route::resource('agency_join_requests', 'AgencyJoinRequestController')->middleware('web-agency-feature');
        }

        Route::resource('requests-for-get-salary', 'GetSalaryRequestController');
        Route::resource('requests-for-get-salary-history', 'GetSalaryRequestFilterationController');
        Route::resource('silver', 'SilverController');

        Route::prefix('coins/{paymentGatwayId}')->group(function () {
            Route::get('/', [CoinController::class, 'index'])->name('coins.index');
            Route::get('/create', [CoinController::class, 'create'])->name('coins.create');
            Route::post('/', [CoinController::class, 'store'])->name('coins.store');
            Route::get('/{id}', [CoinController::class, 'show'])->name('coins.show');
            Route::get('/{id}/edit', [CoinController::class, 'edit'])->name('coins.edit');
            Route::put('/{id}', [CoinController::class, 'update'])->name('coins.update');
            Route::delete('/{id}', [CoinController::class, 'destroy'])->name('coins.destroy');
        });

        Route::prefix('statistics')->name('statistics.')->group(function () {
            Route::get('top-users-data', [AllStatisticController::class, 'topUsersData']);
            Route::get('comparison-user-signup', [AllStatisticController::class, 'comparisonUserSignUp']);
            Route::get('distribution-rooms', [AllStatisticController::class, 'distributionRooms']);
            Route::get('top-room-gifts', [AllStatisticController::class, 'topRoomGifts']);
            Route::get('active-rooms', [AllStatisticController::class, 'averageActiveRooms']);
            Route::get('agency-target', [AllStatisticController::class, 'agencyTarget']);
            Route::get('top-sender', [AllStatisticController::class, 'topSender']);
            Route::get('top-receiver', [AllStatisticController::class, 'topReceiver']);
            Route::get('comparison-agencies-target', [AllStatisticController::class, 'comparisonAgencyTarget']);
            Route::get('room-stats', [AllStatisticController::class, 'roomStats']);
            Route::get('agency-stats', [AllStatisticController::class, 'getStats']);
            Route::get('bd-stats', [AllStatisticController::class, 'getBdStats']);
            Route::get('balance-data', [AllStatisticController::class, 'getBalanceData']);
            Route::get('stats-data', [AllStatisticController::class, 'getStatsData']);
            Route::get('top-followers', [AllStatisticController::class, 'getTopFollowers']);
            Route::get('game-summary', [AllStatisticController::class, 'gameSummary']);
            Route::get('peak-hours', [AllStatisticController::class, 'peakHours'])->name('owner.peak-hours');
            Route::get('rooms-activity', [AllStatisticController::class, 'roomsActivity'])->name('owner.rooms-activity');
            Route::get('top-users-visits', [AllStatisticController::class, 'topUsersVisits'])->name('top-users-visits');
            Route::get('users-online-stats', [AllStatisticController::class, 'onlineStats'])->name('users.online.stats');
        });
        Route::get('/dashboard/finance', [AllStatisticController::class, 'financeIndex']);
        Route::prefix('dashboard')->group(function () {
            Route::get('/finance/cards', [AllStatisticController::class, 'financeCards']);
            Route::get('/finance/tables', [AllStatisticController::class, 'financeTables']);
            Route::get('/finance/chart', [AllStatisticController::class, 'financeChartIndex']);
            Route::get('wallet-logs/ajax', [AllStatisticController::class, 'ajaxWalletLogs'])->name('wallet-logs.ajax');
        });


        Route::resource('coin-logs-reports', CoinLogReportsController::class);


        Route::resource('vip_privilege', 'VipPrivilegeController');
        Route::resource('tickets', 'TicketController');
        Route::resource('pages', 'PageController');
        Route::resource('exchanges', 'ExchangeController');

        Route::get('filter-agencies', App\Admin\Controllers\Filter\AgencyController::class)->name('filter-agencies');

        Route::resource('reports', 'ReportController')->middleware('web-agency-feature');
        Route::get('/Moments-reels', [ReportController::class, 'momentsReels'])
            ->name('admin.ajax.Moments-reels');
        Route::get('/expenses', [ReportController::class, 'expenses']);
        Route::get('/due-salary', [ReportController::class, 'dueSalary'])
            ->name('admin.manager.due-salary');
        Route::resource('charges-reports', 'ChargeReportController');
        Route::get('charge-reports/{agency_id}', [ChargeReportController::class, 'showChargeReports']);
        Route::resource('sallaries', 'SallariesController')->name('index', 'sallaries')->middleware('web-agency-feature');
        Route::resource('total-statistics', 'AllStatisticController');
        Route::resource('coin-reports', 'CoinReportController');
        Route::resource('ban-types', BanTypeController::class);
        Route::resource('sallaries_history', 'SallariesHistoryController');
        // Route::resource ('export-excel','ImportExcelReportController');
        Route::resource('agencies-tareget-manger', AgencyMangerTaregetController::class);
        Route::resource('report_users', ReportFromUsersController::class)->middleware('web-agency-feature');
        Route::post('cashing', 'ReportController@cashing')->name('cashing')->middleware('web-agency-feature');
        Route::resource('trxs', 'CoinLogController');
        Route::resource('images', 'ImageController');


        Route::resource('change-level-histories', ChangeLevelHistoryController::class);
        Route::resource('levels/users', UserLevelController::class)->names([
            'index' => 'levels.users.index',
            'create' => 'levels.users.create',
            'store' => 'levels.users.store',
            'show' => 'levels.users.show',
            'edit' => 'levels.users.edit',
            'update' => 'levels.users.update',
            'destroy' => 'levels.users.destroy',
        ]);
        Route::resource('trashed-users', TrashedUserAccountController::class);
        Route::resource('withdraw-types', WithdrawController::class);
        // Route::resource('agencyMangLink', AgencyMangerLinkController::class);

        // Agency controllers group - only if package installed
        if (\App\Helpers\AgencyPackageHelper::isAgencyInstalled()) {
            Route::prefix('ag')->name('agency.')->namespace('AgencyControllers')->middleware('web-agency-feature')->group(function () {
                Route::get('/', 'HomeController@infoBox')->name('home');
                Route::resource('/users', UserController::class);
                Route::get('professional/users', [UserController::class, 'indexProfessionals']);

                Route::get('/host-diamonds', [HostDiamondController::class, 'index'])->name('hsot-diamond');
                // Route::get('/users/{id}/edit', 'UserController@edit');
                // Route::get('/users/{id}', 'UserController@show');
                Route::get('/userTarget', 'UserTargetController@index')->name('userTarget');
                Route::get('/target', 'AgencyTargetController@index')->name('targets');
                Route::get('/charges', 'ChargeController@index')->name('charges');
                Route::resource('/ag-req', 'AgencyJoinRequestController');
            });
        }

        Route::prefix('ch')->name('charger.')->namespace('ChargerControllers')->group(function () {
            Route::get('/', 'HomeController@infoBox')->name('home');
            Route::get('/charges', 'ChargeController@index')->name('charges');
        });

        Route::resource('/wares_dedicate', 'DedicateWareController')->only('index', 'create', 'store');
        Route::resource('/uuid_dedicate', 'SpecialWareDedicateController');
        Route::get('/bans', [BanController::class, 'index']);
        Route::post('custom-delete-ban', [BanController::class, 'deleteBan']);

        Route::resource('salaries-v2', SalariesController::class)->name('index', 'sallariesV2');

        Route::resource('interests', InterestsController::class);
        Route::resource('custom-settings', CustomController::class);
        Route::get('/custom-page', [AppSitiingCOnfigController::class, 'index'])->name('admin.AppSitiingCOnfigController');
//         Route::get('/setting-family', [FamilyConfigSettingController::class, 'index']);

        // Agency manager routes - only if package installed


        Route::resource('core-wallets', CoreWalletsController::class);
        Route::resource('core-wallet-transactions', CoreWalletTransactionController::class);
        Route::post('/admin/wallet-transfer/submit', [CoreWalletsController::class, 'submitTransfer'])->name('wallet.transfer.submit');



        Route::resource('super-package-rewards', SuperPackageController::class);
        Route::get('admin-rewards-histories', [SuperAdminRewardControllerHistory::class, 'index']);
        Route::get('admin-rewards-histories/{id}', [SuperAdminRewardControllerHistory::class, 'getRewards']);

        Route::get('admin-rewards', [SuperAdminRewardController::class, 'index']);
        Route::get('admin-rewards/{id}', [SuperAdminRewardController::class, 'getRewards']);

        //    dd( Admin::menu(function ($menu) {
        //         $menu->add('Custom Page', ['route' => 'admin.AppSitiingCOnfigController'])
        //             ->icon('fa-file');
        //     }));

        // Route::get('/custom-page', [AppSitiingCOnfigController::class, 'index'])->name('admin.AppSitiingCOnfigController');

        Route::resource('admin-users', AdminUsersController::class);
        Route::resource('parent-users', ParentUsersController::class);
        Route::resource('invitation-code/settings', InvitationSettingsController::class);

        // Agency settings - only if package installed
        if (\App\Helpers\AgencyPackageHelper::isAgencyInstalled()) {
            Route::resource('agency-settings', AgencySettingsController::class)->middleware('web-agency-feature');
        }
        Route::resource('app-feature', FeatureAppController::class);
        Route::resource('zego-feature', ZegoFeatureController::class);
        Route::get('admin-users/{id}/{agency}', 'AdminUsersController@show2');
        //Route::get('percentage-target', [TargetPercentageController::class, 'index'])->name('percentage-target');
        Route::get('convert-is_gold', function () {
            $users = \App\Models\User::where("is_gold_id", 1)->get();
            foreach ($users as $user) {
                $user->image_color_id = 1;
                $user->save();
            }
            dD("goold");
        });

        Route::get('background-count', function () {
            $backgrounds = \Utd\Room\Entities\Background::get();
            if ($backgrounds) {
                foreach ($backgrounds as $background) {
                    $background_count = \Utd\Room\Entities\Room::where("room_background", $background->id)->count();
                    $background->use_count = $background_count;
                    $background->save();
                }
            }
        });

        Route::resource('user-wallets', UserWalletController::class);
        Route::resource('wallet-transactions', WalletTransactionController::class);

        Route::resource('banners', BannerController::class);

        Route::prefix('languages')->group(function () {
            Route::get('/', [LanguageController::class, 'index']);
            Route::put('/{id}', [LanguageController::class, 'update'])->where('id', '[0-9]+');
        });
        Route::resource('settings', SettingController::class)
            ->except(['update'])
            ->names('admin.settings');
        Route::resource('helper-links', LinkViewController::class);

        Route::resource('charges-settings', ChargesSettingController::class);
        Route::post('save_image', [SettingController::class, 'save_image'])->name('save_image');
        Route::resource('notification-templates', NotificationsTemplatesController::class);
        Route::get('/ware-managements/create/{type}', [WareTabController::class, 'create']);
        Route::post('/ware-managements/create', [WareTabController::class, 'store']);
        Route::resource('remaining-diamonds', RemainingDiamondHistoryController::class)->middleware('remaining.diamond.action');
        Route::get('remaining-diamond-settings', [RemainingDiamondSettingController::class, 'index']);
        Route::post('remaining-diamond-settings/save', [RemainingDiamondSettingController::class, 'save'])->name('remaining-diamond-settings.save');
        Route::prefix('ware-management')->group(function () {
            Route::get('/{type?}', [WareTabController::class, 'index']);
            // Route::get('/edit', [WareTabController::class, 'edit'])->where('id', '[0-9]+');
            // Route::put('/{id}', [WareTabController::class, 'update'])->where('id', '[0-9]+');
            // Route::delete('/{id}', [WareTabController::class, 'destroy'])->where('id', '[0-9]+');
        });
        Route::resource('ware-management', WareTabController::class);

        Route::resource('user-charges', UsersChargeController::class);
        //         Route::resource('user-charges-report/{id}', UserChargeReportController::class)->except(['show', 'edit', 'delete']);
        Route::group(['prefix' => 'user-charges-report'], function () {
            Route::get('/{id}', [UserChargeReportController::class, 'index']);
        });

        Route::get('gift-summary', [GiftLogSummaryController::class, 'index']);
        Route::resource('coin-game-users-reports', CoinGameUserAllController::class);
        Route::get('coin-game-users/details', [CoinGameUserAllController::class, 'index_details']);
        Route::get('coin-game-users/show', [CoinGameUserAllController::class, 'showAll']);
        Route::get('coin-game-users/ajax', [CoinGameUserAllController::class, 'ajaxTotals'])
            ->name('coin-game-users.ajax');

        Route::get('/pusher-channels', [PusherStatisticsController::class, 'index'])->name('pusher.channels.index');



        Route::post('/set-preview-area-manager', function () {
            session(['preview_area_manager' => true]);
        });

        Route::post('/unset-preview-area-manager', function () {
            session()->forget('preview_area_manager');
        });

        Route::group(['middleware' => 'local'], function () {
            Route::get('/send-test', [GiftLogTestController::class, 'showGiftForm']);
            Route::post('/send-test', [GiftLogTestController::class, 'gift_queue_cp_view']);

            Route::get('/my-data-test', [GiftLogTestController::class, 'myDataTest']);
            Route::post('/my-data-test', [GiftLogTestController::class, 'mydataTest']);

            Route::get('/relations-test', [GiftLogTestController::class, 'showRelations']);
            Route::post('/relations-test', [GiftLogTestController::class, 'userFriend']);

            Route::get('/visitors-test', [GiftLogTestController::class, 'showVisitors']);
            Route::post('/visitors-test', [GiftLogTestController::class, 'visitorsList']);

            Route::get('/room-admins-test', [GiftLogTestController::class, 'showRoomAdmin']);
            Route::post('/room-admins-test', [GiftLogTestController::class, 'getAdmins']);

            Route::get('/rooms-test', [GiftLogTestController::class, 'showRooms']);
            Route::post('/rooms-test', [GiftLogTestController::class, 'rooms']);

            Route::get('/search-test', [GiftLogTestController::class, 'showSearch']);
            Route::post('/search-test', [GiftLogTestController::class, 'merge_search']);

            Route::get('/notifications-test', [GiftLogTestController::class, 'showNotifications']);
            Route::post('/notifications-test', [GiftLogTestController::class, 'officialMessages']);

            Route::get('/app-settings-test', [GiftLogTestController::class, 'showAppSettings']);
            Route::post('/app-settings-test', [GiftLogTestController::class, 'app_setting']);

            Route::get('/pusher-test/{id}', function ($id) {
                $user = \App\Models\User::findOrFail($id);
                $token = $user->createToken('broadcast')->plainTextToken;

                return view('test.test-pusher', compact('token'));
            });
        });

        Route::prefix('notifications')->group(function () {
            Route::get('count', [App\Http\Controllers\Dashboard\Notification\AdminNotificationController::class, 'count']);
            Route::get('list', [App\Http\Controllers\Dashboard\Notification\AdminNotificationController::class, 'list']);
            Route::post('mark-as-read/{id}', [App\Http\Controllers\Dashboard\Notification\AdminNotificationController::class, 'markAsRead']);
            Route::post('mark-all-read', [App\Http\Controllers\Dashboard\Notification\AdminNotificationController::class, 'markAllRead']);
            Route::get('grid', [NotificationController::class, 'index'])->name('notifications.grid');
        });
        Route::post('/save-fcm-token', function (Illuminate\Http\Request $request) {
            $user = auth()->user();
            $user->fcm_token = $request->token;
            $user->save();
            return response()->json(['status' => 'success']);
        });
    }
);


Route::group([
    'prefix' => 'admin',
    'middleware' => ['web', 'admin'],
], function () {
    Route::post('users/removeBd/{id}', [\App\Admin\Controllers\UserController::class, 'removeBD'])->name('users.remove');
});
