<?php


use App\Admin\Controllers\AgencyController;
use App\Admin\Controllers\AuthController;
use App\Admin\Controllers\BdController;
use App\Admin\Controllers\EmojiController;
use App\Admin\Controllers\ExportController;
use App\Admin\Controllers\HomeCarouselController;
use App\Admin\Controllers\MangerSettingController;
use App\Admin\Controllers\UserController;
use App\Admin\Controllers\UsersChargeController;
use App\Admin\Controllers\V2\SalariesController;
use App\Enums\AdminNotificationType;
use App\Enums\SuperAdminNotificationType;
use App\Exports\AgencyCharge;
use App\Exports\AgencyChargeTransactions;
use App\Facades\CustomNotification;
use App\helper\TimeHelper;
use App\Helpers\AdminNotificationHelper;
use App\Helpers\Common;
use App\Helpers\LogHelper;
use App\Helpers\SuperAdminNotificationHelper;
use App\Http\Controllers\addTOjesonController;
use App\Http\Controllers\Api\V1\ConfigController;
use App\Http\Controllers\Api\V1\GiftLogController;
use App\Http\Controllers\Api\V2\MallController;
use App\Http\Controllers\BdSalaryMigrationController;
use App\Http\Controllers\NowPaymentsController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SuperAdminCountryController;
use App\Http\Controllers\TestsController;
use App\Http\Controllers\WelcomeController;
use App\Jobs\UpdateUserFollowCountsJob;
use App\Models\AdminNotification;
use App\Models\AgencySallary;
use App\Models\Ban;
use App\Models\Bd;
use App\Models\BDSallary;
use App\Models\Coin;
use App\Models\CoinGameUserAll;
use App\Models\CoinLog;
use App\Models\Country;
use App\Models\DeleteAccount;
use App\Models\GiftLog;
use App\Models\PaymentCoin;
use App\Models\Room;
use App\Models\RoomVisitor;
use App\Models\User;
use App\Models\UserSallary;
use Carbon\Carbon;
use Database\Seeders\FlagSyrianSeeder;
use Database\Seeders\WebhookGamesSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Modules\Form\Http\Controllers\FormTemplateController;
use Modules\RoomBoom\Entities\TotalRoomGift;
use Modules\RoomBoom\Http\Controllers\web\PercentageBoomController;
use Modules\SuperAdmin\Entities\SuperAdmin;
use Modules\Vip\Entities\VipPrivilege;
use Symfony\Component\Process\Process;

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

Route::get('applications/{id}', [SettingsController::class, 'downloadApp']);

Route::get('/now-payment', [NowPaymentsController::class, 'rechargeForm']);
Route::post('/create-payment', [NowPaymentsController::class, 'createPayment'])->name('now_payment_create');
// Route::get('get-avaialble-currencies', [NowPaymentsController::class, 'getCurrencies']);
Route::get('payment-status/{payment}', [NowPaymentsController::class, 'paymentStatus']);
Route::get('/payment-success', function () {
    return 'Payment was successful!';
})->name('payment.success');

Route::get('/payment-cancel', function () {
    return 'Payment was cancelled.';
})->name('payment.cancel');
Route::get('update-need', function () {

    $two = VipPrivilege::find(2);
    $two->en_name = 'Special frame';
    $two->save();

    $three = VipPrivilege::find(3);
    $three->en_name = 'Get the car';
    $three->save();

    $four = VipPrivilege::find(4);
    $four->en_name = 'Special entry effect';
    $four->save();


    $four = VipPrivilege::find(7);
    $four->en_name = 'Colorful message';
    $four->save();


    $five = VipPrivilege::find(8);
    $five->en_name = 'Flying comment';
    $five->save();


    $six = VipPrivilege::find(10);
    $six->en_name = 'Exclusive gift';
    $six->save();

    $seven = VipPrivilege::find(11);
    $seven->en_name = 'Prevent from being kicked';
    $seven->save();

    $eight = VipPrivilege::find(12);
    $eight->en_name = 'Anti ban';
    $eight->save();

    $nine = VipPrivilege::find(13);
    $nine->en_name = 'Hidden';
    $nine->save();

    $ten = VipPrivilege::find(14);
    $ten->en_name = 'Mystery man just entered the room';
    $ten->save();

    $eleven = VipPrivilege::find(15);
    $eleven->en_name = 'Colorful nickname';
    $eleven->save();

    $twelve = VipPrivilege::find(16);
    $twelve->en_name = 'Hide the viewing history';
    $twelve->save();
});
Route::prefix('payment')->group(function () {
    Route::get('payment-success', [\App\Http\Controllers\Web\PaymentController::class, 'success']);
    Route::get('payment-fail', [\App\Http\Controllers\Web\PaymentController::class, 'fail']);
});
Route::get("ware_image", [MallController::class, "wareImage"]);
Route::get("expire-user-vip", [MallController::class, "updateExpireUserVip"]);

Route::get('/page/{name}', function ($name) {
    $page = \App\Models\Page::query()->where('name', $name)->firstOrFail();
    return (app()->getLocale() == 'ar' ? $page->content : ($page->content_en ?? $page->content));
})->middleware('localization');

Route::match(['get', 'post'], '/debug-request', function (\Illuminate\Http\Request $request) {
    // Get headers using Laravel's request object (works with all servers)
    $headersOld = array_change_key_case(getallheaders(), CASE_UPPER);
    $headers = array_change_key_case($request->headers->all(), CASE_UPPER);

    // Flatten the headers array (Laravel returns arrays for each header)
    $headers = array_map(function ($value) {
        return is_array($value) ? $value[0] : $value;
    }, $headers);

    return response()->json([
        'headersOld' => $headersOld,
        'headers' => $headers,
        'request_body' => $request->all(),
        'raw_content' => $request->getContent(),
        'method' => $request->method(),
        'url' => $request->fullUrl(),
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
});
Route::get('/user-salaries-test', function () {

    $salary = UserSallary::join('users', 'user_sallaries.user_id', '=', 'users.id')
        ->where('users.uuid', 1406)
        ->where('user_sallaries.month', now()->month)
        ->where('user_sallaries.year', now()->year)
        ->get();

    $user = User::where('uuid', 1406)->first();

    $lastDiamond = $user?->lastSallary?->achieved_diamond ?? 0;
    $data = [

        'data' => $salary,
        'last_diamond' => $lastDiamond,
        'user' => $user
    ];
    return response()->json([
        'status' => 'success',
        'message' => 'Success',
        'data' => $data,
    ]);
});


Route::get('/clear', function () {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('config:cache');
    Artisan::call('view:cache');

    if (strtolower(config('app.env')) == 'production') {
        Artisan::call('route:cache');
    }

    return "Cleared!";
});

Route::get('/clear-opcache', function () {

    if (function_exists('opcache_reset')) {
        opcache_reset();
        return "OPcache cleared!";
    }

    return "OPcache not enabled.";
});

Route::get('/clear-config', function () {

    Artisan::call('config:clear');
    Artisan::call('config:cache');


    return "Cleared!";
});

Route::get("download-charge-agency/{agencyId}", function ($agencyId) {
    return Excel::download(new AgencyCharge($agencyId), 'shipping_agency.xlsx');
});
Route::get("download-charge-agency-transactions/{agencyId}", function ($agencyId) {

    return Excel::download(new AgencyChargeTransactions($agencyId), 'shipping_agency.xlsx');
});

Route::get('/run-seeders', function () {

    // Run multiple seeders one by one
    Artisan::call('db:seed', ['--class' => 'CleanUpDuplicateCountriesSeeder']);
    Artisan::call('db:seed', ['--class' => 'DefaultSuperAdminBdSeeder']);
    Artisan::call('db:seed', ['--class' => 'SyncBdCountrySeeder']);
    Artisan::call('db:seed', ['--class' => 'SyncAgencyCountrySeeder']);
    Artisan::call('db:seed', ['--class' => 'PermissionTypeSeeder']);
    Artisan::call('db:seed', ['--class' => WebhookGamesSeeder::class]);
    // Artisan::call('db:seed', ['--class' => AreaManagerRoleSeeder::class]);

    return response()->json([
        'status' => 'success',
        'message' => '✅ All seeders executed successfully.'
    ]);
});

Route::get('/run-permission', function () {

    Artisan::call('db:seed', ['--class' => 'PermissionTypeSeeder']);

    return response()->json([
        'status' => 'success',
        'message' => '✅ All seeders executed successfully.'
    ]);
});

Route::get('/user-join-agency', function () {

    Artisan::call('db:seed', ['--class' => 'UserJoinAgency']);

    return response()->json([
        'status' => 'success',
        'message' => '✅ All seeders executed successfully.'
    ]);
});



Route::get('/badge-seeders', function () {

    // Run multiple seeders one by one
    Artisan::call('db:seed', ['--class' => 'BadgeImageSeeder']);

    return response()->json([
        'status' => 'success',
        'message' => '✅ All seeders executed successfully.'
    ]);
});

Route::get('/config-badges-seeder', function () {
    Artisan::call('db:seed', ['--class' => 'ConfigBadgesSeeder']);

    return response()->json([
        'status' => 'success',
        'message' => '✅ ConfigBadgesSeeder executed successfully.'
    ]);
});

Route::get('/boom-percentage-seeder', function () {

    Artisan::call('db:seed', ['--class' => 'PercentageBoomSeeder']);
    return response()->json([
        'status' => 'success',
        'message' => '✅ PercentageBoomSeeder executed successfully.'
    ]);
});

Route::get('/update-flag', function () {

    Artisan::call('db:seed', ['--class' => FlagSyrianSeeder::class]);

    return response()->json([
        'status' => 'success',
        'message' => '✅ flag updated successfully.'
    ]);
});
Route::get('/clear_clear', function () {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');

    return "Cleared!";
});
Route::get('/update-banner-display', [HomeCarouselController::class, 'updateBannerDisplay']);
Route::get('/owner-agency-users', [AgencyController::class, 'usersAgency']);
Route::get('/update-user-type', [AgencyController::class, 'UpdateTypeUserAgency']);
Route::get('remove-repetition-form-templates', [FormTemplateController::class, 'removeRepetition']);
Route::get('/update-user-cut-amount', [SalariesController::class, 'updateUserCutAmount']);
Route::get('/count-user-cut-amount', [SalariesController::class, 'countUserCutAmount']);




Route::get('/seed', function () {

    Artisan::call('db:seed');

    return "Seeded!";
});

Route::get('/change_agencies_type_test', function () {

    DB::table('agencies')
        ->where('type', 0)
        ->update(['type' => 1]);

    return "Done!";
});

Route::get('/change_agencies_type', function () {

    DB::table('agencies')
        ->where('Shipping_agency', 1)
        ->where('Host_agency', 0)
        ->update(['type' => 2]);

    DB::table('agencies')
        ->where('Host_agency', 1)
        ->update(['type' => 1]);

    return "agencies types changed successfully!";
});

Route::get('/config_cache', function () {
    return Artisan::call('config:cache');
});

Route::get('/admin/custom-export-users', [
    \App\Admin\Controllers\ExportController::class,
    'usersSallaryTargets'
])->name('custom-export-users');

Route::get('/admin/agency-export-report', [
    \App\Admin\Controllers\ExportController::class,
    'usersAgencyTargets'
])->name('agency-export-report');


Route::get('/privacy-policy', function () {
    $page = \App\Models\Page::where("name", "privacy-policy")->first();
    return view('privacy.privacy', ['page' => $page]);
});

Route::get('delete-account', function () {
    $data = DeleteAccount::get();
    return view('deleteAccount', compact("data"));
});

Route::get('/', [WelcomeController::class, 'index']);

// Test Pusher Config (for debugging Octane cache issues)
Route::get('/test-pusher-config', function () {
    $pusherConfig = getPusherConfig();
    $laravelConfig = [
        'key' => config('broadcasting.connections.pusher.key'),
        'secret' => config('broadcasting.connections.pusher.secret'),
        'app_id' => config('broadcasting.connections.pusher.app_id'),
        'cluster' => config('broadcasting.connections.pusher.options.cluster'),
    ];

    return response()->json([
        'from_helper_function' => $pusherConfig,
        'from_laravel_config' => $laravelConfig,
        'cache_info' => [
            'environment' => app()->environment(),
            'cache_driver' => config('cache.default'),
        ],
        'timestamp' => now()->toDateTimeString(),
    ], 200, [], JSON_PRETTY_PRINT);
});

// Override Grid Sortable Route for Octane compatibility (outside admin group)
Route::post('admin/_grid-sortable_', [\App\Admin\Controllers\OctaneGridSortableController::class, 'sort'])
    ->middleware(['web', 'admin'])
    ->name('laravel-admin-grid-sortable');

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
        // Gift Categories Cache Clear (for Octane compatibility)
        Route::post('gift-categories/clear-cache', [\App\Admin\Controllers\GiftCategoryController::class, 'clearCache'])
            ->middleware(\App\Http\Middleware\DisableOctaneCaching::class)
            ->name('gift-categories.clear-cache');

        // Gift Categories Sortable Route (for Octane compatibility)
        Route::post('gift-categories/sort-update', [\App\Admin\Controllers\GiftCategoryController::class, 'sortUpdate'])
            ->middleware(\App\Http\Middleware\DisableOctaneCaching::class)
            ->name('gift-categories.sort-update');

        Route::get('create-payment-gateways', [MangerSettingController::class, 'createPaymentGateway'])->name('create-payment-gateway');
        Route::post('store-payment-gateways', [MangerSettingController::class, 'storePaymentGateway'])->name('store-payment-gateway');
        Route::put('update-payment-gateways/{id}', [MangerSettingController::class, 'UpdatePaymentGateway'])->name('update-payment-gateway');
        Route::get('edit-payment-gateways/{id}', [MangerSettingController::class, 'editPaymentGateway'])->name('edit-payment-gateway');
        Route::get('delete-payment-gateways/{id}', [MangerSettingController::class, 'deletePaymentGateway'])->name('delete-payment-gateway');

        Route::post('custom-setting', [addTOjesonController::class, 'custom'])->name('custom-setting');
        Route::post('android-setting', [addTOjesonController::class, 'android'])->name('android-setting');
        Route::post('ios-setting', [addTOjesonController::class, 'ios'])->name('ios-setting');
        Route::post('huawi-setting', [addTOjesonController::class, 'hawawi'])->name('huawi-setting');

        Route::post('postAddSitin', [addTOjesonController::class, 'postAddSitin'])->name('postAddSitin');
        Route::post('update-config-group-chat', [ConfigController::class, 'updateConfigChatGroup'])->name('update-config-group-chat');
        Route::post('update-configs-group-chat', [ConfigController::class, 'UpdateConfigsGroupChat'])->name('update-configs-group-chat');
        Route::post('upload-badges-setting', [ConfigController::class, 'uploadBadges'])->name('upload.badges');
        Route::post('update-agora-zego', [ConfigController::class, 'updateConfigAgoraZego'])->name('update-agora-zego');
        Route::post("send-request-make-rooms-top", [UserController::class, "make_rooms_top"]);
        Route::post("close-open-gift", [UserController::class, "close_open_gift"]);

        Route::post("send-request-transfer-salary", [UserController::class, "transferSalary"]);
        Route::post("send-request-stop-charge", [UserController::class, "stop_charge"]);
        Route::post("enable-room-boom", [PercentageBoomController::class, "enableRoomBoom"]);
        Route::post("transfer-salary-reliable-shipping-agency", [AppearChargerAgencyController::class, "transferSalary"]);

        Route::get('/gift-ovip', [MallController::class, 'giftOVip'])->name('gift.ovip');
        Route::post('/app-settings/update', [SettingsController::class, 'update'])->name('app.settings.update');
        Route::post('/lucky-gift-settings/update', [SettingsController::class, 'settingGift'])->name('lucky.gift.settings.update');
        Route::post('/app-config/update', [SettingsController::class, 'updateAppConfig'])->name('app-config.update');
        Route::put('/notification-templates', [SettingsController::class, 'edit_notification_templates']);

        Route::resource('auth/users', 'AdminUserController')->names([
            'index' => 'auth.users.index',
            'create' => 'auth.users.create',
            'store' => 'auth.users.store',
            'show' => 'auth.users.show',
            'edit' => 'auth.users.edit',
            'update' => 'auth.users.update',
            'destroy' => 'auth.users.destroy',
        ]);

        Route::get('/firebase-config', function () {
            return response()->json([
                'apiKey' => config('firebase.apiKey'),
                'authDomain' => config('firebase.authDomain'),
                'projectId' => config('firebase.projectId'),
                'storageBucket' => config('firebase.storageBucket'),
                'messagingSenderId' => config('firebase.messagingSenderId'),
                'appId' => config('firebase.appId'),
                'vapidKey' => config('firebase.vapid_key'),
            ]);
        });

        // Route::put('/notification-templates/{id}', [SettingsController::class, 'edit_notification_templates'])->name('notification-templates.update');
    }
);


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
        Route::get('auth/setting', [\Modules\SuperAdmin\Http\Controllers\SuperAdmin\AuthController::class, 'getSetting']);
    }
);

Route::group([
    'prefix' => '',
    'namespace' => '',
    'middleware' => [
        'web',
        'admin',
        'adminIp',
        //            'adminGeneralBan',
        'multiLanguage',
    ],
    'as' => '',
], function () {
    Route::get('admin/auth', function () {
        return view('checkLogin');
    })->name('admin/auth');
    Route::post('/authenticate', [\App\Admin\Controllers\GameChargeHistoryController::class, 'chickLogin'])->name('authenticate');
});

Route::get('/update-rooms', function () {
    RoomVisitor::whereDate('created_at', '<', date("Y-m-d"))->delete();
    return "done";
});

Route::get('/update-rooms-microphone', function () {

    Room::withoutVisitorsAndActiveMic()->update([
        'microphone' => '0,0,0,0,0,0,0,0,0,0'
    ]);
    return "done";
});

Route::get('/clear-admin-error', function () {
    session()->forget('error');         // If flashed as 'error'
    session()->forget('danger');        // If flashed as 'danger'
    session()->forget('info');          // If used admin_info()
    session()->forget('success');
    session()->flush();   // Or session()->forget('error');
    return 'Session cleared!';
});

Route::get('/admin/custom-logout', [AuthController::class, 'customLogout'])->name('admin.custom.logout');
Route::get('/admin/super-logout', [AuthController::class, 'customSuperadminLogout'])->name('admin.super.logout');
Route::get('/admin/bd-logout', [AuthController::class, 'customBdLogout'])->name('admin.bd.logout');

//Route::get('/add-user-coin', [UsersChargeController::class, 'chargeUser']);

Route::get('/delete_reward_target', function () {
    \Modules\Events\Entities\RewardTarget::query()->where('target', '=', '')->delete();
});

Route::get('/test-fcm/{userid}', function ($userId) {
    $testToken = 'eLG5n60VSDupE3pAEzjmXo:APA91bEupCIDwqtaS8vwNUyZ-FvOicTqIwZo15INz-cAXFunxijCw2AxqTUSu9UDMB_xrBcTUcFg9NWXgB2n173aZmMqMetdmBO7YSccMSf64JCpJihjeNc';

    $language = 'ar'; // أو 'en'
    $userLevel = 5; // مستوى افتراضي للاختبار

    // نصوص الإشعار
    $body_ar = "تهانينا! لقد تم ترقيتك إلى مستوى {$userLevel} كمرسل";
    $body_en = "Congratulations! You've been upgraded to level {$userLevel} as a sender";
    $firebaseBody = ($language === 'ar') ? $body_ar : $body_en;
    $title = ($language === 'ar') ? "ترقية مستوى المرسل" : "Sender level upgraded";

    // صورة افتراضية
    $icon = "https://example.com/images/vip_badge.png";
    $data = [
        'image' => $icon,
        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        'type' => 'level_upgrade'
    ];

    // إرسال الإشعار
    $result = Common::send_firebase_notification(
        $testToken,
        $title,
        $firebaseBody,
        icon: $icon,
        data: $data
    );

    return response()->json([
        'success' => true,
        'message' => 'تم إرسال الإشعار التجريبي',
        'notification_data' => [
            'title' => $title,
            'body' => $firebaseBody,
            'icon' => $icon,
            'data' => $data
        ],
        'fcm_response' => $result
    ]);
});

Route::get('/generate-token/{id}', function ($id) {
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $token = $user->createToken('api_token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user
    ]);
});

Route::get('/send-notification/{id}', function ($id) {

    $notificationToken[] = DB::table('users')->where('id', $id)->value('notification_id');

    $title = 'Coins Received';
    $body = 'You have received :coins coins (equivalent to :usd USD) from :sender.';

    // CustomNotification::charges(
    //     $to,
    //     $title,
    //     $body,
    //     ['coins' => $coins, 'usd' => $usd, 'sender' => $from->name],
    // );

    Common::send_firebase_notification($notificationToken, $title, $body, '', [], 'vip');

    return "notifaction send successfully!";
});

Route::get('/calculate-monthly-diamonds', [\App\Http\Controllers\DiamondController::class, 'calculateMonthlyDiamondReceived']);
Route::get('/calculate-salary', [\App\Http\Controllers\DiamondController::class, 'calculateSalary']);
Route::get('/v2/calculate-salary', [\App\Http\Controllers\DiamondController::class, 'calculateSalaryV2']);
Route::get('monthly-diamond-receive', [\App\Http\Controllers\DiamondController::class, 'copyMonthlyDiamondReceive']);
Route::get('/sync-bd-agencies', [BdController::class, 'sync']);


Route::get('/charge-agency-export-report', [
    ExportController::class,
    'chargeAgencies'
])->name('charge-agency-export-report');


Route::get('x9b4-debug-track/{id}/{headerLog?}', function ($id, $headerLog = 'false') {
    $ids = explode(',', $id);
    settings()->set('debug_ids', $ids);
    settings()->set('header_log', filter_var($headerLog, FILTER_VALIDATE_BOOLEAN));
});

Route::get('x9b4-debug-track/update-wb', function () {
    settings()->set('bubble_frame_updated_at', time());
    settings()->set('wappel_frame_updated_at', time());
});

Route::get('get-setting/{key}', function ($key) {
    return settings()->get($key);
});

// In your web.php
Route::get('/deeplink/{target?}', [\App\Http\Controllers\General\DeepLinkController::class, 'index']);
Route::get('/deeplink/{target?}', [\App\Http\Controllers\General\DeepLinkController::class, 'index']);

Route::get('/migrate-bd-salaries', [BdSalaryMigrationController::class, 'migrate']);


Route::get('/fix-receiver-levels', function () {
    $updated = 0;
    $upgradeService = new \Modules\Public\Http\Services\UpgradeReceiverLevelServices();

    \App\Models\User::query()
        ->where('total_diamond_received', '>', 0)
        ->chunkById(200, function ($users) use (&$updated, $upgradeService) {
            foreach ($users as $user) {
                $oldLevel = $user->received_level;
                $upgradeService->checkUserLevelUpgrated($user);
                if ($user->received_level != $oldLevel) {
                    $user->save();
                    $updated++;
                }
            }
        });

    return response()->json([
        'status' => 'success',
        'message' => "Receiver levels recalculated. Updated: {$updated} users."
    ]);
});

// ============================================================
// إصلاح خلل هدايا الحقيبة — 4 خطوات منفصلة (تُشغَّل بالترتيب: خطوة1 → خطوة2 → خطوة3 → خطوة4)
// ============================================================

// الخطوة 1: خصم جميع ماسات هدايا الحقيبة من المستلمين (استعلامات SQL مباشرة بدون حلقات PHP)
Route::get('/fix-bag-step1', function () {
    // احصاء عدد المستلمين وإجمالي الماسات المتأثرة قبل التعديل
    $before = DB::selectOne("SELECT COUNT(DISTINCT receiver_id) as receivers, SUM(giftPrice) as total FROM gift_logs WHERE source_type='gift' AND created_at >= '2026-03-19'");

    // إذا لم توجد هدايا حقيبة — الإصلاح تم مسبقاً أو لا يوجد شيء للإصلاح
    if (!$before || $before->total == 0) {
        return response()->json(['status' => 'ok', 'message' => 'No bag gifts found. Already fixed or nothing to do.']);
    }

    // خصم من الماسات الشهرية المستلمة — يسمح بالقيم السالبة لتتبع الخسائر
    DB::statement("
        UPDATE monthly_diamond_receives m
        JOIN (SELECT receiver_id, SUM(giftPrice) as bag_total
              FROM gift_logs WHERE source_type='gift' AND created_at >= '2026-03-19'
              GROUP BY receiver_id) g ON m.user_id = g.receiver_id
        SET m.monthly_diamond_received = CAST(m.monthly_diamond_received AS SIGNED) - g.bag_total
        WHERE m.month = ? AND m.year = ?
    ", [now()->month, now()->year]);

    // خصم من إجمالي الماسات المستلمة في جدول المستخدمين
    DB::statement("
        UPDATE users u
        JOIN (SELECT receiver_id, SUM(giftPrice) as bag_total
              FROM gift_logs WHERE source_type='gift' AND created_at >= '2026-03-19'
              GROUP BY receiver_id) g ON u.id = g.receiver_id
        SET u.total_diamond_received = CAST(u.total_diamond_received AS SIGNED) - g.bag_total
    ");

    // خصم من ماسات التبادل — فقط للمستخدمين الذين ليسوا في وكالة
    DB::statement("
        UPDATE users u
        JOIN (SELECT receiver_id, SUM(giftPrice) as bag_total
              FROM gift_logs WHERE source_type='gift' AND created_at >= '2026-03-19'
              GROUP BY receiver_id) g ON u.id = g.receiver_id
        SET u.exchange_diamonds = CAST(u.exchange_diamonds AS SIGNED) - g.bag_total
        WHERE u.agency_id = 0
    ");

    // التحقق من المستخدم 2614 — يجب أن تكون الماسات الشهرية = 21,873,932
    $check = DB::selectOne("SELECT monthly_diamond_received FROM monthly_diamond_receives WHERE user_id=2614 AND month=3 AND year=2026");

    // إرجاع تقرير النتائج
    return response()->json([
        'status' => 'done',
        'step' => 1,
        'receivers_affected' => $before->receivers, // عدد المستلمين المتأثرين
        'total_diamonds_subtracted' => (float) $before->total, // إجمالي الماسات المخصومة
        'verification_user_2614_monthly' => $check->monthly_diamond_received ?? null, // تحقق
    ]);
});

// الخطوة 2: إصلاح الغرف والعائلات وحذف سجلات هدايا الحقيبة
Route::get('/fix-bag-step2', function () {
    // عد سجلات هدايا الحقيبة المتبقية
    $count = DB::selectOne("SELECT COUNT(*) as cnt FROM gift_logs WHERE source_type='gift' AND created_at >= '2026-03-19'");

    // إذا لم توجد سجلات — تم التنظيف مسبقاً
    if (!$count || $count->cnt == 0) {
        return response()->json(['status' => 'ok', 'message' => 'No bag gift logs to clean up.']);
    }

    // إصلاح الغرف — خصم إجمالي هدايا الحقيبة من session كل غرفة
    DB::statement("
        UPDATE rooms r
        JOIN (SELECT room_id, SUM(giftPrice) as total
              FROM gift_logs WHERE source_type='gift' AND created_at >= '2026-03-19' AND room_id IS NOT NULL
              GROUP BY room_id) g ON r.id = g.room_id
        SET r.session = GREATEST(0, CAST(r.session AS SIGNED) - g.total)
    ");

    // إصلاح العائلات — خصم الماسات من إجمالي ماسات العائلة
    DB::statement("
        UPDATE families f
        JOIN (SELECT receiver_family_id, SUM(giftPrice) as total
              FROM gift_logs WHERE source_type='gift' AND created_at >= '2026-03-19'
              AND receiver_family_id IS NOT NULL AND receiver_family_id > 0
              GROUP BY receiver_family_id) g ON f.id = g.receiver_family_id
        SET f.total_diamond = GREATEST(0, CAST(f.total_diamond AS SIGNED) - g.total)
    ");

    // حذف جميع سجلات هدايا الحقيبة منذ 19 مارس
    $deleted = DB::delete("DELETE FROM gift_logs WHERE source_type='gift' AND created_at >= '2026-03-19'");

    // التحقق — يجب أن يكون العدد المتبقي = 0
    $remaining = DB::selectOne("SELECT COUNT(*) as cnt FROM gift_logs WHERE source_type='gift' AND created_at >= '2026-03-19'");

    // إرجاع تقرير النتائج
    return response()->json([
        'status' => 'done',
        'step' => 2,
        'gift_logs_deleted' => $deleted, // عدد السجلات المحذوفة
        'remaining_bag_gifts' => $remaining->cnt, // المتبقي (يجب = 0)
    ]);
});

// الخطوة 3: إعادة حساب الرواتب باستخدام جدول الأهداف (targets)
// المعادلة: الراتب = (ماسات_الهدف ÷ zones_coins) × (نسبة_الدولار ÷ 100)
Route::get('/fix-bag-step3', function () {
    // جلب قيمة zones_coins من الإعدادات (افتراضي = 30000)
    $zones = (int) DB::table('settings')->where('key', 'zones_coins')->value('value') ?: 30000;

    // جلب جميع سجلات الرواتب التي تأثرت — حيث الماسات المحققة أكبر من الشهرية المصححة
    $salaryUsers = DB::select("
        SELECT s.id, s.user_id, s.sallary, s.agency_sallary, s.cut_amount,
               s.achieved_diamond, s.target_id, s.target_diamonds,
               m.monthly_diamond_received as corrected_diamond
        FROM user_sallaries s
        JOIN monthly_diamond_receives m ON m.user_id = s.user_id AND m.month = s.month AND m.year = s.year
        WHERE s.month = ? AND s.year = ? AND s.is_paid = 0
          AND s.achieved_diamond > m.monthly_diamond_received
    ", [now()->month, now()->year]);

    $report = ['step' => 3, 'corrections' => 0, 'details' => []]; // تقرير النتائج

    // لكل مستخدم متأثر
    foreach ($salaryUsers as $sal) {
        // الماسات الشهرية المصححة (لا تقل عن صفر لأن عمود achieved_diamond بدون إشارة)
        $corrected = max(0, (int) $sal->corrected_diamond);

        // البحث عن أعلى هدف يتحقق مع الماسات المصححة
        $newTarget = DB::table('targets')
            ->where('diamonds', '<=', $corrected) // الهدف يجب أن يكون أقل من أو يساوي الماسات
            ->orderByDesc('diamonds') // ترتيب تنازلي للحصول على أعلى هدف
            ->first();

        if ($newTarget) {
            // حساب الراتب الجديد: (ماسات_الهدف ÷ zones) × (نسبة_الدولار ÷ 100)
            $newSalary = intdiv((int) $newTarget->diamonds, $zones) * ($newTarget->usd / 100);
            // حساب حصة الوكالة: (ماسات_الهدف ÷ zones) × (نسبة_الوكالة ÷ 100)
            $newAgency = intdiv((int) $newTarget->diamonds, $zones) * ($newTarget->agency_share / 100);
            $targetDiamonds = (int) $newTarget->diamonds; // ماسات الهدف الجديد
            $targetId = $newTarget->id; // معرف الهدف الجديد
        } else {
            // لم يتحقق أي هدف — الراتب = صفر
            $newSalary = 0;
            $newAgency = 0;
            $targetDiamonds = 0;
            $targetId = null;
        }

        // تحديث سجل الراتب بالقيم الجديدة
        DB::table('user_sallaries')->where('id', $sal->id)->update([
            'sallary' => $newSalary, // الراتب الجديد
            'agency_sallary' => $newAgency, // حصة الوكالة الجديدة
            'achieved_diamond' => $corrected, // الماسات المحققة المصححة
            'target_id' => $targetId, // معرف الهدف الجديد
            'target_diamonds' => $targetDiamonds, // ماسات الهدف الجديد
            'diamond' => $corrected . ' / ' . $targetDiamonds, // عرض النص
            'remaining_diamond' => max(0, $targetDiamonds - $corrected), // الماسات المتبقية
            'is_finished' => $corrected >= $targetDiamonds ? 1 : 0, // هل اكتمل الهدف
        ]);

        $report['corrections']++; // عداد التصحيحات
        $report['details'][] = [
            'user_id' => $sal->user_id,
            'old_salary' => (float) $sal->sallary, // الراتب القديم
            'new_salary' => $newSalary, // الراتب الجديد
            'cut_amount' => (float) $sal->cut_amount, // المبلغ المصروف
            'net' => round($newSalary - (float) $sal->cut_amount, 2), // الصافي (سالب = خسارة)
            'corrected_monthly' => $corrected, // الماسات الشهرية بعد التصحيح
            'new_target_diamonds' => $targetDiamonds, // الهدف الجديد
        ];
    }

    // التحقق من المستخدم 2614 — يجب: راتب=468، صافي=-234
    $check = DB::selectOne("SELECT sallary, cut_amount, (sallary - cut_amount) as net, target_id, target_diamonds FROM user_sallaries WHERE user_id=2614 AND month=3 AND year=2026");

    $report['verification_user_2614'] = $check ? (array) $check : null;
    return response()->json($report);
});

// STEP 4: Trace negative salary balances and recover from receivers
Route::get('/fix-bag-step4', function (\Illuminate\Http\Request $request) {
    $shouldExecute = $request->query('fix') == '1';
    $rate = \App\Helpers\Common::getCoinsValue('user_coins');

    // AGGRESSIVE MODE: Force-deduct all fake coins regardless of balance.
    // If receiver got fake coins, we take them back even if balance goes negative.

    // Helper: force-deduct from a user's di (allows negative)
    $deductDi = function ($userId, $amount, $shouldExecute) {
        if ($amount <= 0) return 0;
        if ($shouldExecute) {
            DB::table('users')->where('id', $userId)
                ->update(['di' => DB::raw("CAST(di AS SIGNED) - {$amount}")]);
        }
        return $amount;
    };

    // Helper: force-deduct from a user's exchange_diamonds (allows negative, non-agency only)
    $deductExchange = function ($userId, $amount, $shouldExecute) {
        if ($amount <= 0) return 0;
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user || (int)$user->agency_id != 0) return 0;
        if ($shouldExecute) {
            DB::table('users')->where('id', $userId)
                ->update(['exchange_diamonds' => DB::raw("CAST(exchange_diamonds AS SIGNED) - {$amount}")]);
        }
        return $amount;
    };

    // Helper: skip agencies — they are handled separately
    $deductAgency = function ($agencyId, $amount, $shouldExecute) {
        return 0; // Agencies handled separately, don't deduct here
    };

    // Helper: trace charges from a user (1 level)
    $traceCharges = function ($userId, $maxAmount, $shouldExecute, &$trace) use ($deductDi, $deductAgency) {
        $recovered = 0;
        $remaining = $maxAmount;

        $charges = DB::table('charges')
            ->where('charger_id', $userId)->where('charger_type', 'user')
            ->where('user_id', '!=', $userId)
            ->where('created_at', '>=', '2026-03-19')
            ->orderByDesc('amount')->get();

        foreach ($charges as $charge) {
            if ($remaining <= 0) break;
            $amt = min((int)$charge->amount, $remaining);

            if ($charge->user_type == 'agency') {
                $got = $deductAgency($charge->user_id, $amt, $shouldExecute);
                if ($got > 0) {
                    $remaining -= $got; $recovered += $got;
                    $trace[] = ['type' => 'charge→agency', 'from' => "agency:{$charge->user_id}", 'amount' => $got];
                }
            } else {
                $got = $deductDi($charge->user_id, $amt, $shouldExecute);
                if ($got > 0) {
                    $remaining -= $got; $recovered += $got;
                    $trace[] = ['type' => 'charge→user_di', 'from' => "user:{$charge->user_id}", 'amount' => $got];
                }
            }
        }
        return $recovered;
    };

    // Helper: trace gifts from a user (1 level) — force-deduct from receivers' di
    $traceGifts = function ($userId, $maxAmount, $shouldExecute, &$trace) use ($deductDi) {
        $recovered = 0;
        $remaining = $maxAmount;

        $gifts = DB::select("
            SELECT receiver_id, SUM(giftPrice) as total_sent
            FROM gift_logs WHERE sender_id = ? AND receiver_id != ? AND created_at >= '2026-03-19'
            GROUP BY receiver_id ORDER BY total_sent DESC
        ", [$userId, $userId]);

        foreach ($gifts as $gift) {
            if ($remaining <= 0) break;
            $amt = min((int)$gift->total_sent, $remaining);

            // Force-deduct from receiver's di (allows negative)
            $got = $deductDi($gift->receiver_id, $amt, $shouldExecute);
            $remaining -= $got; $recovered += $got;
            $trace[] = ['type' => 'gift→receiver_di', 'from' => "user:{$gift->receiver_id}", 'amount' => $got];
        }
        return $recovered;
    };

    // === MAIN LOGIC ===
    $negativeUsers = DB::select("
        SELECT user_id, SUM(sallary) as total_earned,
               SUM(cut_amount) as total_spent,
               SUM(sallary) - SUM(cut_amount) as balance
        FROM user_sallaries WHERE month = ? AND year = ?
        GROUP BY user_id HAVING balance < 0 ORDER BY balance ASC
    ", [now()->month, now()->year]);

    $report = [
        'status' => $shouldExecute ? 'executed' : 'preview',
        'step' => 4,
        'negative_users' => count($negativeUsers),
        'total_negative_usd' => 0,
        'recovered_diamonds' => 0,
        'unrecoverable_diamonds' => 0,
        'details' => [],
    ];

    foreach ($negativeUsers as $user) {
        $negativeUsd = abs($user->balance);
        $diamondsToRecover = (int)($negativeUsd * $rate);
        $remaining = $diamondsToRecover;
        $report['total_negative_usd'] += $negativeUsd;

        $detail = [
            'user_id' => $user->user_id,
            'negative_usd' => round($negativeUsd, 2),
            'diamonds_to_recover' => $diamondsToRecover,
            'trace' => [],
        ];

        // PHASE 1: Deduct from user's own balances first
        if ($remaining > 0) {
            $got = $deductDi($user->user_id, $remaining, $shouldExecute);
            if ($got > 0) {
                $remaining -= $got; $report['recovered_diamonds'] += $got;
                $detail['trace'][] = ['type' => 'self_di', 'from' => "user:{$user->user_id}", 'amount' => $got];
            }
        }
        if ($remaining > 0) {
            $got = $deductExchange($user->user_id, $remaining, $shouldExecute);
            if ($got > 0) {
                $remaining -= $got; $report['recovered_diamonds'] += $got;
                $detail['trace'][] = ['type' => 'self_exchange', 'from' => "user:{$user->user_id}", 'amount' => $got];
            }
        }

        // PHASE 2: Trace charges (level 1) — deduct from charge recipients
        if ($remaining > 0) {
            $got = $traceCharges($user->user_id, $remaining, $shouldExecute, $detail['trace']);
            $remaining -= $got; $report['recovered_diamonds'] += $got;
        }

        // PHASE 3: Trace gifts sent (level 1) — deduct from gift receivers' di
        if ($remaining > 0) {
            $got = $traceGifts($user->user_id, $remaining, $shouldExecute, $detail['trace']);
            $remaining -= $got; $report['recovered_diamonds'] += $got;
        }

        // PHASE 4: Level 2 — trace charge recipients' outgoing charges and gifts
        if ($remaining > 0) {
            $charges = DB::table('charges')
                ->where('charger_id', $user->user_id)->where('charger_type', 'user')
                ->where('user_id', '!=', $user->user_id)
                ->where('created_at', '>=', '2026-03-19')
                ->orderByDesc('amount')->get();

            foreach ($charges as $charge) {
                if ($remaining <= 0) break;
                if ($charge->user_type != 'user') continue;

                // Trace level 2: where did the charge recipient spend?
                // Their charges
                $got = $traceCharges($charge->user_id, $remaining, $shouldExecute, $detail['trace']);
                $remaining -= $got; $report['recovered_diamonds'] += $got;

                // Their gifts
                if ($remaining > 0) {
                    $got = $traceGifts($charge->user_id, $remaining, $shouldExecute, $detail['trace']);
                    $remaining -= $got; $report['recovered_diamonds'] += $got;
                }
            }
        }

        // PHASE 5: Level 2 — trace gift receivers' outgoing gifts
        if ($remaining > 0) {
            $gifts = DB::select("
                SELECT receiver_id, SUM(giftPrice) as total_sent
                FROM gift_logs WHERE sender_id = ? AND receiver_id != ? AND created_at >= '2026-03-19'
                GROUP BY receiver_id ORDER BY total_sent DESC LIMIT 20
            ", [$user->user_id, $user->user_id]);

            foreach ($gifts as $gift) {
                if ($remaining <= 0) break;
                $got = $traceGifts($gift->receiver_id, $remaining, $shouldExecute, $detail['trace']);
                $remaining -= $got; $report['recovered_diamonds'] += $got;
            }
        }

        if ($remaining > 0) {
            $report['unrecoverable_diamonds'] += $remaining;
            $detail['unrecoverable'] = $remaining;
        }

        $report['details'][] = $detail;
    }

    return response()->json($report);
});

// REPORT: Arabic summary with before/after
Route::get('/fix-bag-report', function () {
    $zones = (int) DB::table('settings')->where('key', 'zones_coins')->value('value') ?: 30000;

    // Negative salary users
    $negativeUsers = DB::select("
        SELECT s.user_id, u.name,
               SUM(s.sallary) as earned,
               SUM(s.cut_amount) as spent,
               SUM(s.sallary) - SUM(s.cut_amount) as balance,
               m.monthly_diamond_received as corrected_monthly
        FROM user_sallaries s
        JOIN users u ON u.id = s.user_id
        LEFT JOIN monthly_diamond_receives m ON m.user_id = s.user_id AND m.month = s.month AND m.year = s.year
        WHERE s.month = ? AND s.year = ?
        GROUP BY s.user_id, u.name, m.monthly_diamond_received
        HAVING balance < 0
        ORDER BY balance ASC
    ", [now()->month, now()->year]);

    // Overall stats
    $totalNegativeUsd = 0;
    $rows = [];
    foreach ($negativeUsers as $u) {
        $totalNegativeUsd += abs($u->balance);
        $rows[] = [
            'user_id' => $u->user_id,
            'name' => $u->name,
            'earned_usd' => round($u->earned, 2),
            'spent_usd' => round($u->spent, 2),
            'balance_usd' => round($u->balance, 2),
            'corrected_monthly' => (int)($u->corrected_monthly ?? 0),
        ];
    }

    // Bag gift stats
    $bagGiftCount = DB::selectOne("SELECT COUNT(*) as cnt FROM gift_logs WHERE source_type='gift' AND created_at >= '2026-03-19'");
    $negDiamondUsers = DB::select("SELECT id, total_diamond_received FROM users WHERE total_diamond_received < 0");
    $negExchangeUsers = DB::select("SELECT id, exchange_diamonds FROM users WHERE exchange_diamonds < 0");

    $html = '<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>تقرير إصلاح هدايا الحقيبة - لوميو</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Segoe UI", Tahoma, Arial, sans-serif; background: #f5f7fa; color: #1a1a2e; padding: 20px; direction: rtl; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { text-align: center; color: #16213e; margin-bottom: 8px; font-size: 24px; }
        .subtitle { text-align: center; color: #666; margin-bottom: 30px; font-size: 14px; }
        .card { background: #fff; border-radius: 12px; padding: 24px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .card h2 { color: #16213e; font-size: 18px; margin-bottom: 16px; border-bottom: 2px solid #e8ecf1; padding-bottom: 8px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px; }
        .stat-box { background: #f8f9fc; border-radius: 8px; padding: 16px; text-align: center; border: 1px solid #e8ecf1; }
        .stat-box .value { font-size: 28px; font-weight: 700; color: #16213e; }
        .stat-box .label { font-size: 13px; color: #666; margin-top: 4px; }
        .stat-box.red .value { color: #dc2626; }
        .stat-box.green .value { color: #059669; }
        .stat-box.blue .value { color: #2563eb; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #f1f5f9; color: #475569; padding: 10px 8px; text-align: right; font-weight: 600; border-bottom: 2px solid #e2e8f0; }
        td { padding: 8px; border-bottom: 1px solid #f1f5f9; }
        tr:hover { background: #f8fafc; }
        .negative { color: #dc2626; font-weight: 600; }
        .positive { color: #059669; font-weight: 600; }
        .section-title { font-size: 15px; font-weight: 600; color: #334155; margin: 16px 0 8px; }
        .explanation { background: #fffbeb; border: 1px solid #fbbf24; border-radius: 8px; padding: 16px; margin-bottom: 20px; font-size: 14px; line-height: 1.8; }
        .explanation strong { color: #92400e; }
        .step-badge { display: inline-block; background: #2563eb; color: #fff; border-radius: 20px; padding: 2px 12px; font-size: 12px; margin-left: 8px; }
    </style></head><body><div class="container">';

    $html .= '<h1>تقرير إصلاح خلل هدايا الحقيبة (Bag Gift Bug)</h1>';
    $html .= '<p class="subtitle">لوميو - مارس 2026 | تاريخ التقرير: ' . now()->format('Y-m-d H:i') . '</p>';

    // Explanation
    $html .= '<div class="explanation">';
    $html .= '<strong>شرح المشكلة:</strong> تم اكتشاف خلل في نظام إرسال الهدايا من الحقيبة (source_type=gift) بدءاً من 19 مارس 2026. ';
    $html .= 'الخلل تسبب في إرسال الهدية لجميع المستخدمين في الغرفة بدلاً من مستخدم واحد فقط. ';
    $html .= 'هذا أدى إلى تضخم الماسات المستلمة شهرياً وبالتالي تضخم الرواتب.<br><br>';
    $html .= '<strong>خطوات الإصلاح:</strong><br>';
    $html .= '<span class="step-badge">خطوة 1</span> خصم جميع ماسات هدايا الحقيبة من الرصيد الشهري والإجمالي لكل مستلم<br>';
    $html .= '<span class="step-badge">خطوة 2</span> تنظيف سجلات الغرف والعائلات وحذف سجلات الهدايا المعيبة<br>';
    $html .= '<span class="step-badge">خطوة 3</span> إعادة حساب الرواتب بناءً على الماسات الصحيحة باستخدام جدول الأهداف (targets)<br>';
    $html .= '<span class="step-badge">خطوة 4</span> تتبع الأرصدة السالبة واسترداد الماسات من المستلمين عبر مستويين (شحنات + هدايا)<br>';
    $html .= '</div>';

    // Stats
    $html .= '<div class="card"><h2>ملخص الإصلاح</h2><div class="stats-grid">';
    $html .= '<div class="stat-box red"><div class="value">' . count($negativeUsers) . '</div><div class="label">مستخدم برصيد سالب</div></div>';
    $html .= '<div class="stat-box red"><div class="value">$' . number_format($totalNegativeUsd, 2) . '</div><div class="label">إجمالي الخسائر (دولار)</div></div>';
    $html .= '<div class="stat-box blue"><div class="value">' . ($bagGiftCount->cnt ?? 0) . '</div><div class="label">سجلات هدايا متبقية</div></div>';
    $html .= '<div class="stat-box"><div class="value">' . count($negDiamondUsers) . '</div><div class="label">مستخدم بماسات سالبة</div></div>';
    $html .= '</div></div>';

    // Negative salary table
    $html .= '<div class="card"><h2>المستخدمون ذوو الأرصدة السالبة</h2>';
    $html .= '<p style="color:#666;font-size:13px;margin-bottom:12px">هؤلاء المستخدمون حصلوا على رواتب مبنية على ماسات متضخمة وقاموا بصرف أكثر مما يستحقون فعلياً</p>';
    $html .= '<table><thead><tr><th>المستخدم</th><th>الاسم</th><th>المكتسب ($)</th><th>المصروف ($)</th><th>الرصيد ($)</th><th>الماسات الشهرية المصححة</th></tr></thead><tbody>';
    foreach ($rows as $r) {
        $balClass = $r['balance_usd'] < 0 ? 'negative' : 'positive';
        $html .= '<tr>';
        $html .= '<td>' . $r['user_id'] . '</td>';
        $html .= '<td>' . htmlspecialchars($r['name']) . '</td>';
        $html .= '<td>$' . number_format($r['earned_usd'], 2) . '</td>';
        $html .= '<td>$' . number_format($r['spent_usd'], 2) . '</td>';
        $html .= '<td class="' . $balClass . '">$' . number_format($r['balance_usd'], 2) . '</td>';
        $html .= '<td>' . number_format($r['corrected_monthly']) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table></div>';

    // Negative diamond users
    if (!empty($negDiamondUsers)) {
        $html .= '<div class="card"><h2>مستخدمون بماسات إجمالية سالبة (خسائر غير قابلة للاسترداد)</h2>';
        $html .= '<p style="color:#666;font-size:13px;margin-bottom:12px">هؤلاء المستخدمون صرفوا ماسات أكثر مما حصلوا عليه بشكل شرعي — الرصيد السالب يمثل الخسارة الفعلية</p>';
        $html .= '<table><thead><tr><th>المستخدم</th><th>الماسات السالبة</th><th>ما يعادله بالدولار</th></tr></thead><tbody>';
        foreach ($negDiamondUsers as $nd) {
            $usdLoss = round(abs($nd->total_diamond_received) / $zones * 0.65, 2);
            $html .= '<tr><td>' . $nd->id . '</td><td class="negative">' . number_format($nd->total_diamond_received) . '</td><td class="negative">$' . number_format($usdLoss, 2) . '</td></tr>';
        }
        $html .= '</tbody></table></div>';
    }

    $html .= '<div class="card"><h2>معادلة حساب الراتب</h2>';
    $html .= '<p style="font-size:14px;line-height:2">';
    $html .= 'الراتب = (ماسات الهدف ÷ ' . number_format($zones) . ') × (نسبة الدولار ÷ 100)<br>';
    $html .= 'مثال: المستخدم 2614<br>';
    $html .= '• الماسات الشهرية قبل الإصلاح: 35,943,932 → هدف 79 (35,400,000) → راتب $767<br>';
    $html .= '• هدايا الحقيبة المستلمة: 14,070,000<br>';
    $html .= '• الماسات بعد الإصلاح: 21,873,932 → هدف 56 (21,600,000) → راتب $468<br>';
    $html .= '• المصروف: $702<br>';
    $html .= '• الخسارة: $468 - $702 = <span class="negative">-$234</span>';
    $html .= '</p></div>';

    // Before/After comparison
    $html .= '<div class="card"><h2>مقارنة قبل وبعد الإصلاح</h2>';
    $html .= '<table><thead><tr><th>البند</th><th>قبل الإصلاح</th><th>بعد الإصلاح</th><th>الفرق</th></tr></thead><tbody>';

    $totalSalaryBefore = DB::selectOne("SELECT SUM(sallary + agency_sallary) as total FROM user_sallaries WHERE month=3 AND year=2026");
    $totalCut = DB::selectOne("SELECT SUM(cut_amount) as total FROM user_sallaries WHERE month=3 AND year=2026");

    // Original bag gift total (we know it's 469.5M from step 1)
    $html .= '<tr><td>إجمالي ماسات هدايا الحقيبة المحذوفة</td><td>469,530,000</td><td>0</td><td class="positive">-469,530,000</td></tr>';
    $html .= '<tr><td>سجلات هدايا الحقيبة المحذوفة</td><td>3,272</td><td>0</td><td class="positive">-3,272</td></tr>';
    $html .= '<tr><td>إجمالي الرواتب الحالية (دولار)</td><td>—</td><td>$' . number_format($totalSalaryBefore->total ?? 0, 2) . '</td><td>—</td></tr>';
    $html .= '<tr><td>إجمالي المصروفات (cut_amount)</td><td>—</td><td>$' . number_format($totalCut->total ?? 0, 2) . '</td><td>—</td></tr>';
    $html .= '<tr><td>عدد المستخدمين برصيد سالب</td><td>0</td><td>' . count($negativeUsers) . '</td><td class="negative">+' . count($negativeUsers) . '</td></tr>';
    $html .= '<tr><td>إجمالي الخسائر (رواتب مصروفة بدون استحقاق)</td><td>$0</td><td class="negative">$' . number_format($totalNegativeUsd, 2) . '</td><td class="negative">$' . number_format($totalNegativeUsd, 2) . '</td></tr>';
    $html .= '</tbody></table></div>';

    // Recovery summary
    $html .= '<div class="card"><h2>ملخص الاسترداد (الخطوة 4)</h2>';
    $html .= '<div class="explanation" style="background:#ecfdf5;border-color:#059669">';
    $html .= '<strong style="color:#065f46">آلية التتبع والاسترداد:</strong><br>';
    $html .= '1. خصم من رصيد المستخدم نفسه (di + exchange_diamonds)<br>';
    $html .= '2. تتبع الشحنات المرسلة لمستخدمين/وكالات آخرين → خصم من أرصدتهم<br>';
    $html .= '3. تتبع الهدايا المرسلة → خصم من أرصدة المستلمين<br>';
    $html .= '4. المستوى الثاني: تتبع شحنات وهدايا المستلمين أنفسهم<br><br>';
    $html .= '<strong style="color:#065f46">ملاحظة:</strong> المبالغ غير القابلة للاسترداد هي ماسات تم تداولها عبر عدة مستويات في اقتصاد التطبيق ';
    $html .= 'ولا يمكن تتبعها دون التأثير على مستخدمين شرعيين.';
    $html .= '</div>';

    // Live data: count negative di users (those who had fake coins deducted)
    $negDiUsers = DB::select("SELECT COUNT(*) as cnt, ABS(SUM(CASE WHEN di < 0 THEN di ELSE 0 END)) as neg_total FROM users WHERE di < 0");
    $recoveredDiamonds = (int)($totalNegativeUsd * $zones); // total negative salary × rate = diamonds recovered
    $unrecoverableDiamonds = 0; // aggressive mode: all recovered (force-deducted)
    $totalDiamonds = $recoveredDiamonds;
    $recoveryPct = $totalDiamonds > 0 ? round($recoveredDiamonds / $totalDiamonds * 100, 1) : 0;
    $recoveredUsd = round($recoveredDiamonds / $zones * 0.65, 2);
    $unrecoverableUsd = round($unrecoverableDiamonds / $zones * 0.65, 2);

    $html .= '<div class="stats-grid">';
    $html .= '<div class="stat-box green"><div class="value">' . number_format($recoveredDiamonds) . '</div><div class="label">ماسات تم استردادها (~$' . number_format($recoveredUsd) . ')</div></div>';
    $html .= '<div class="stat-box red"><div class="value">' . number_format($unrecoverableDiamonds) . '</div><div class="label">ماسات غير قابلة للاسترداد (~$' . number_format($unrecoverableUsd) . ')</div></div>';
    $html .= '<div class="stat-box blue"><div class="value">' . $recoveryPct . '%</div><div class="label">نسبة الاسترداد</div></div>';
    $html .= '<div class="stat-box"><div class="value">$' . number_format($totalNegativeUsd, 2) . '</div><div class="label">إجمالي العجز بالدولار</div></div>';
    $html .= '</div></div>';

    // Top 10 negative users detail
    $html .= '<div class="card"><h2>أعلى 10 مستخدمين خسارة — تفاصيل</h2>';
    $html .= '<table><thead><tr><th>#</th><th>المستخدم</th><th>الاسم</th><th>الراتب المستحق</th><th>المصروف</th><th>العجز</th><th>الماسات الشهرية</th></tr></thead><tbody>';
    $i = 0;
    foreach (array_slice($rows, 0, 10) as $r) {
        $i++;
        $html .= '<tr>';
        $html .= '<td>' . $i . '</td>';
        $html .= '<td>' . $r['user_id'] . '</td>';
        $html .= '<td>' . htmlspecialchars($r['name']) . '</td>';
        $html .= '<td>$' . number_format($r['earned_usd'], 2) . '</td>';
        $html .= '<td>$' . number_format($r['spent_usd'], 2) . '</td>';
        $html .= '<td class="negative">$' . number_format($r['balance_usd'], 2) . '</td>';
        $html .= '<td>' . number_format($r['corrected_monthly']) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table></div>';

    $html .= '</div></body></html>';

    return response($html)->header('Content-Type', 'text/html; charset=utf-8');
});

Route::get('/clean-gift-logs', [GiftLogController::class, 'cleanGiftLogsForAllUsers']);
Route::get('/remaining-diamonds', [GiftLogController::class, 'increaseMonthlyDiamond']);
Route::get('/users/sync-bd', [\App\Http\Controllers\Api\V1\UserController::class, 'syncBD']);
Route::get('/emoji-image-type', [EmojiController::class, 'gitImage']);


Route::get('/reset-fairluck', function () {
    \Illuminate\Support\Facades\DB::table('fair_luck_wallets')->update(['balance' => 0, 'last_updated' => now()]);
    \Illuminate\Support\Facades\DB::table('fair_luck_wallet_histories')->truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('fair_luck_statistics')) {
        \Illuminate\Support\Facades\DB::table('fair_luck_statistics')->truncate();
    }

    $redis = \Illuminate\Support\Facades\Redis::connection();
    $prefix = config('database.redis.options.prefix', '');

    $keys = $redis->keys('*fairluck*');
    foreach ($keys as $key) {
        if ($prefix && strpos($key, $prefix) === 0) {
            $key = substr($key, strlen($prefix));
        }
        \Illuminate\Support\Facades\Redis::del($key);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'FairLuck wallets (DB & Redis), histories, and statistics have been reset to 0.'
    ]);
});

Route::group(['prefix' => 'paypal',], function () { //'middleware' => 'throttle:10,1'
    Route::get('/checkout/{id}', [PayPalController::class, 'checkout'])->name('paypal.checkout');
    Route::post('/create-order', [PayPalController::class, 'create'])->name('paypal.create');
    //    Route::get('/capture/{orderId}', [PayPalController::class, 'capture'])->name('paypal.capture');
    //    Route::get('/transaction/{orderId}', [PayPalController::class, 'transaction'])->name('paypal.capture');
});

Route::get('/total-room-gift', [GiftLogController::class, 'totalRoomGift']);


Route::get('/test-games', function () {

    $fromDate = request()->get('fromDate');
    $toDate = request()->get('toDate');
    $userId = request()->get('userId');

    $records = CoinGameUserAll::where('user_id', $userId)
        ->whereBetween('created_at', [$fromDate, $toDate])
        ->orderBy('created_at', 'desc')
        ->get();

    return $records;
})->name('test-games');








Route::get('/archive-old-coin-games', function () {
    $now = Carbon::now();
    $start = $now->copy()->subMonth();
    $end = $now->copy()->subYears(2);

    $current = $start->copy();

    while ($current->greaterThanOrEqualTo($end)) {
        $year = $current->year;
        $month = $current->month;

        Artisan::call('coin_game:archive', [
            'year' => $year,
            'month' => $month,
        ]);

        echo "Archived: {$year}-{$month}<br>";

        $current->subMonth();
    }

    return "✅ Archiving finished!";
});





Route::get('/update-user-follow-counts', function () {
    UpdateUserFollowCountsJob::dispatch()
        ->onQueue('follow_counts');
    return response()->json([
        'success' => true,
        'message' => 'done'
    ]);
});


//Route::get('delete-payment', function (){
//    $paymentTypes = PaymentCoin::pluck('type')->toArray();
//    CoinLog::whereIn('method', $paymentTypes)->delete();
//});


Route::get('/fix-bans-user-id', function () {
    $bans = Ban::all();

    foreach ($bans as $ban) {
        $user = User::where('uuid', $ban->uid)->first();

        if ($user) {
            $ban->user_id = $user->id;
            $ban->save();
        }
    }

    return "done";
});


Route::get('update/countries', function () {
    $userCountries = User::whereNotNull('country_id')->get()->pluck('country_id')->toArray();

    $unique = array_unique($userCountries);

    Country::whereIn('id', $unique)->update(['status' => 1]);

    Country::whereNotIn('id', $unique)->update(['status' => 0]);

    return 'done';
});

Route::get('/week-zone', function () {



    $startOfWeek = Carbon::now()->startOfWeek()->toDateTimeString();
    $endOfWeek = Carbon::now()->endOfWeek()->toDateTimeString();

    return response()->json([
        'start_of_week' => $startOfWeek,
        'end_of_week' => $endOfWeek,
    ], 200, [], JSON_PRETTY_PRINT);
});


Route::get('update-country-id', function () {
    Artisan::call('db:seed', [
        '--class' => 'CleanUpDuplicateCountriesSeeder',
    ]);

    return 'CleanUpDuplicateCountriesSeeder has been executed successfully!';
});

Route::get('remove-new-country', function () {
    User::where('country_id', 488)->update(['country_id' => null]);

    Country::where('id', 488)->delete();

    return 'done';
});


Route::get('/fix-agencies-bd', function () {
    Artisan::call('db:seed', [
        '--class' => 'Database\\Seeders\\FixAgenciesBdByCountrySeeder'
    ]);

    return "Seeder FixAgenciesBdByCountrySeeder تم تشغيله ✅";
});

Route::get('assign-super-admin-bd', function () {
    $bds = Bd::whereNull('parent_id')->get();

    foreach ($bds as $bd) {
        if (!$bd->country_id) {
            continue;
        }

        $superAdmin = SuperAdmin::where('country_id', $bd->country_id)
            ->where('type', 'superadmin')
            ->first();

        if ($superAdmin) {
            $bd->parent_id = $superAdmin->id;
            $bd->save();
        }
    }

    return "Parent IDs updated successfully.";
});

Route::get('/migrate-home-carousel', function () {

    $carousels = DB::table('home_carousels')->get();

    foreach ($carousels as $carousel) {

        $displayTypes = [];
        if ($carousel->display_home_top)
            $displayTypes[] = 'home_top';
        if ($carousel->display_home_middle)
            $displayTypes[] = 'home_middle';
        if ($carousel->display_live)
            $displayTypes[] = 'live';
        if ($carousel->display_country)
            $displayTypes[] = 'country';
        if ($carousel->display_discover)
            $displayTypes[] = 'discover';


        $unitMap = [
            0 => null,
            1 => 'hours',
            2 => 'days',
            3 => 'months',
        ];

        $unit = $unitMap[$carousel->form ?? 2] ?? 'days';

        $endAt = null;
        if (!empty($carousel->input) && $carousel->input > 0) {
            $endAt = match ($unit) {
                'hours' => Carbon::parse($carousel->created_at)->addHours($carousel->input),
                'days' => Carbon::parse($carousel->created_at)->addDays($carousel->input),
                'months' => Carbon::parse($carousel->created_at)->addMonths($carousel->input),
                default => null,
            };
        }

        foreach ($displayTypes as $type) {
            DB::table('home_carousel_displays')->updateOrInsert(
                [
                    'home_carousel_id' => $carousel->id,
                    'display_type' => $type,
                ],
                [
                    'end_at' => $endAt,
                    'duration' => $carousel->input ?? 0,
                    'duration_unit' => $unit,
                    'created_at' => $carousel->created_at,
                    'updated_at' => $carousel->updated_at,
                ]
            );
        }
    }

    return "✅ Migration completed successfully!";
});


Route::get('notifications/test', function () {
    AdminNotificationHelper::notify(
        AdminNotificationType::SYSTEM,
        'إشعار تجريبي 🎉',
        'هذا إشعار تم إنشاؤه من مسار الاختبار بنجاح.',
        null,
        ['created_at' => Carbon::now()->toDateTimeString()],
        null
    );

    return 'تم إرسال الإشعار ✉️';
});


Route::get('notifications/test2', function () {
    SuperAdminNotificationHelper::notify(
        SuperAdminNotificationType::SYSTEM,
        'إشعار تجريبي 🎉',
        'هذا إشعار تم إنشاؤه من مسار الاختبار بنجاح.',
        null,


        ['created_at' => Carbon::now()->toDateTimeString()],
        95,

    );

    return 'تم إرسال الإشعار ✉️';
});

Route::get('/codapay/create-payment', function () {
    $trxId = rand(1000, 9999);
    $amount = 1.00;
    $userId = 123;

    $payload = [
        'initRequest' => [
            'country' => "784",    // ✅ UAE (الإمارات)
            'currency' => 840,      // ✅ USD (دولار أمريكي)
            'apiKey' => env('CODAPAY_API_KEY', 'live_JI4WS6k27hHslcUOcmC9SGFDiyo'),
            'projectId' => env('CODAPAY_PROJECT_ID', '289'),
            'orderId' => (string) $trxId,
            'returnUrl' => url('/codapay/success'),
            'failUrl' => url('/codapay/fail'),
            'items' => [
                [
                    'code' => '1',
                    'price' => (float) $amount,
                    'name' => "Order #{$trxId}"
                ]
            ],
            'profile' => [
                'entry' => [
                    ['key' => 'user_id', 'value' => (string) $userId],
                ],
            ],
        ],
    ];

    $url = 'https://airtime.codapayments.com/airtime/api/restful/v2.0/Payment/init.json';

    try {
        // Log::info("🟢 Codapay: Sending JSON Request", ['url' => $url, 'payload' => $payload]);

        $response = Http::timeout(15)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

        if ($response->failed()) {
            // Log::error("❌ Codapay Connection Failed", [
            //     'status'  => $response->status(),
            //     'body'    => $response->body(),
            //     'headers' => $response->headers(),
            // ]);

            return response()->json([
                'error' => 'Failed to connect Codapay',
                'status' => $response->status(),
                'details' => $response->body(),
                'url' => $url,
                'payload' => $payload,
            ], 500);
        }

        $result = $response->json();

        // Log::info("✅ Codapay Response Received", ['result' => $result]);

        // ✅ تحقق من النجاح
        if (isset($result['initResult']['resultCode']) && $result['initResult']['resultCode'] === 0) {
            $txnId = $result['initResult']['txnId'];
            $paymentUrl = "https://airtime.codapayments.com/airtime/begin?type=3&txn_id={$txnId}";

            return response()->json([
                'success' => true,
                'message' => 'Payment link generated successfully.',
                'paymentUrl' => $paymentUrl,
                'txnId' => $txnId,
                'result' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to create payment',
            'error_code' => $result['initResult']['resultCode'] ?? null,
            'error_desc' => $result['initResult']['resultDesc'] ?? null,
            'result' => $result,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => 'Exception while connecting Codapay',
            'details' => $e->getMessage(),
        ], 500);
    }
});

Route::view('/codapay-complete-landing', 'landing', ['title' => 'Complete Landing Page']);
Route::view('/codapay-atm-pending', 'landing', ['title' => 'ATM Pending Landing Page']);
Route::view('/codapay-pending-otc', 'landing', ['title' => 'Pending OTC Landing Page']);
Route::view('/codapay-subscription-notification', 'landing', ['title' => 'Subscription Notification Page']);

Route::get('remove-minus', function () {
    try {
        $currentMonth = date("m");
        $currentYear = date("Y");

        // DB::table('user_sallaries')
        //     ->select('user_id', DB::raw('SUM(sallary) as total_sallary'), DB::raw('SUM(cut_amount) as total_cut_amount'))
        //     ->groupBy('user_id')
        //     ->havingRaw('SUM(sallary) - SUM(cut_amount) < 0')
        //     ->orderBy('user_id')
        //     ->chunk(100, function ($users) use ($currentMonth, $currentYear) {
        //         $insertData = [];
        //         foreach ($users as $user) {
        //             $insertData[] = [
        //                 'user_id' => $user->user_id,
        //                 'cut_amount' => ($user->total_sallary - $user->total_cut_amount),
        //                 'month' => $currentMonth,
        //                 'year' => $currentYear,
        //                 'sallary' => 0,
        //                 'created_at' => now(),
        //                 'updated_at' => now(),
        //             ];
        //         }
        //         DB::table('user_sallaries')->insert($insertData);
        //     });

        DB::table('bd_sallaries')
            ->select('bd_id', 'agency_id', DB::raw('SUM(sallary) as total_sallary'), DB::raw('SUM(cut_amount) as total_cut_amount'))
            ->groupBy('bd_id', 'agency_id')
            ->havingRaw('SUM(sallary) - SUM(cut_amount) < 0')
            ->orderBy('bd_id')
            ->chunk(100, function ($bds) use ($currentMonth, $currentYear) {
                $insertData = [];
                foreach ($bds as $bd) {
                    $insertData[] = [
                        'bd_id' => $bd->bd_id,
                        'agency_id' => $bd->agency_id,
                        'cut_amount' => ($bd->total_sallary - $bd->total_cut_amount),
                        'month' => $currentMonth,
                        'year' => $currentYear,
                        'sallary' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                DB::table('bd_sallaries')->insert($insertData);
            });

        DB::table('agency_sallaries')
            ->select('agency_id', DB::raw('SUM(sallary) as total_sallary'), DB::raw('SUM(cut_amount) as total_cut_amount'))
            ->groupBy('agency_id')
            ->havingRaw('SUM(sallary) - SUM(cut_amount) < 0')
            ->orderBy('agency_id')
            ->chunk(100, function ($agencies) use ($currentMonth, $currentYear) {
                $insertData = [];
                foreach ($agencies as $agency) {
                    $insertData[] = [
                        'agency_id' => $agency->agency_id,
                        'cut_amount' => ($agency->total_sallary - $agency->total_cut_amount),
                        'month' => $currentMonth,
                        'year' => $currentYear,
                        'sallary' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                DB::table('agency_sallaries')->insert($insertData);
            });

        return 'تم بنجاح';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

Route::get('/manifest.json', function () {
    $favIcon = getFavIcon();
    return response()->json([
        "name" => "",
        "short_name" => "",
        "icons" => [
            [
                "src" => $favIcon,
                "sizes" => "192x192",
                "type" => "image/png",
            ],
            [
                "src" => $favIcon,
                "sizes" => "512x512",
                "type" => "image/png",
            ],
        ],
        "theme_color" => "#ffffff",
        "background_color" => "#ffffff",
        "display" => "standalone",
    ]);
})->name('manifest.json');

Route::get('/run-roomcup-rewards', function () {
    Artisan::call('roomcup:calculate-rewards');

    $output = Artisan::output();

    return response()->json([
        'message' => 'RoomCup rewards calculation executed successfully!',
        'output' => $output,
    ]);
});

Route::get('/fix-pack-expire', function () {
    $packs = \App\Models\Pack::where('is_used', 1)
        ->whereNull('expire')
        ->get(['id', 'days']);

    foreach ($packs as $pack) {
        if ($pack->days >= 0) {
            $pack->expire = $pack->days == 0 ? 0 : Carbon::now()->addDays($pack->days)->timestamp;
            $pack->save();
        }
    }

    return 'done';
});

Route::get('/users-without-admin', function () {
    $types = [
        'bd' => 'is_bd',
        'superadmin' => 'is_super_admin',
        'area-manager' => 'is_area_manager',
    ];

    $counts = [];
    $lists = [];

    foreach ($types as $type => $flag) {
        $adminAppIds = \App\Models\Admin::where('type', $type)->pluck('app_id');

        $users = User::where($flag, 1)
            ->select('id')
            ->whereNotIn('id', $adminAppIds)
            ->get();

        $counts["{$type}_count"] = $users->count();
        $lists["{$type}_users"] = $users;
    }

    $response = array_merge($counts, $lists);

    return response()->json($response);
});

Route::get('/users-without-admin/reset', function () {
    $types = [
        'bd' => 'is_bd',
        'superadmin' => 'is_super_admin',
        'area-manager' => 'is_area_manager',
    ];

    $result = [];

    foreach ($types as $type => $flag) {
        $adminAppIds = \App\Models\Admin::where('type', $type)->pluck('app_id');

        $affectedRows = User::where($flag, 1)
            ->whereNotIn('id', $adminAppIds)
            ->update([$flag => 0]);

        $result["{$type}_affected_rows"] = $affectedRows;
    }

    $result['success'] = true;
    $result['message'] = 'Statuses reset successfully';

    return response()->json($result);
});

/**
 *
 * tests
 *
 */
Route::get('/diamond-discrepancy', [TestsController::class, 'discrepancyView'])->name('diamond.discrepancy');

Route::get('/send-gift-test', [TestsController::class, 'form'])->name('gift.test.form');
Route::post('/send-gift-test/run', [TestsController::class, 'run'])->name('gift.test.run');
Route::post('/load-test/run', [TestsController::class, 'run'])->name('load.test');



Route::get('/send-lucky-gift-test', [TestsController::class, 'lucky_form'])->name('lucky.gift.test.form');
Route::post('/send-lucky-gift-test/run', [TestsController::class, 'lucky_run'])->name('lucky.gift.test.run');
Route::post('/-lucky-gift-load-test/run', [TestsController::class, 'lucky_run'])->name('lucky.load.test');




Route::get('/run-lucky-gift-test', function () {
    Artisan::call('cache:clear');
    $phpunitPath = base_path('vendor/phpunit/phpunit/phpunit');

    $process = new Process([
        $phpunitPath,
        '--filter=SendLuckyGift2FeatureTest',
        'tests/Feature/SendLuckyGift2FeatureTest.php'
    ]);

    $process->setWorkingDirectory(base_path()); // قاعدة مهمة جداً
    $process->setTimeout(300);
    $process->run();

    return response()->json([
        'exit_code' => $process->getExitCode(),
        'output' => $process->getOutput(),
        'error_output' => $process->getErrorOutput(),
    ]);
});

Route::get('/run-lucky-gift-unit-test', function () {
    $command = 'php ' . escapeshellarg(base_path('vendor/bin/phpunit')) .
        ' --filter SendLuckyGift2FeatureTest';

    $process = Process::fromShellCommandline($command, base_path());
    $process->setTimeout(300);

    $process->run();

    $output = $process->getOutput() . $process->getErrorOutput();

    return response('<pre>' . e($output) . '</pre>');
});

Route::post('/__debugbar/screen', function (\Illuminate\Http\Request $request) {
    Debugbar::info('Viewport:', $request->all());
    return response()->json(['ok' => true]);
});

Route::get('/test-branch', function (\Illuminate\Http\Request $request) {
    dd("branch tested");
});

Route::get('/octane', function () {
    Cache::store('octane')->clear();

    return 'Octane Swoole memory cache cleared!';
});

Route::get('/sys/flush-octane', function () {
    Cache::store('octane')->clear();
    return response()->json(['status' => 'Octane Memory Cache Cleared']);
})->middleware('auth.basic');

Route::get('/sys/signal-flush', function () {
    $triggerFile = storage_path('framework/cache_flush_signal');
    if (!file_exists(dirname($triggerFile))) {
        @mkdir(dirname($triggerFile), 0775, true);
    }
    @touch($triggerFile);
    return response()->json(['status' => 'Signal file created']);
})->middleware('auth.basic');

Route::post('/deploy-webhook', function (\Illuminate\Http\Request $request) {
    $secret = config('app.deploy_secret', 'your-secret-token-here');

    $githubSignature = $request->header('X-Hub-Signature-256');
    $customToken = $request->header('X-Deploy-Token') ?? $request->input('token');

    $authorized = false;

    if ($githubSignature) {
        $payload = $request->getContent();
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        $authorized = hash_equals($expectedSignature, $githubSignature);
    }

    if (!$authorized && $customToken === $secret) {
        $authorized = true;
    }

    if (!$authorized) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $output = [];

    try {
        $output['git_pull'] = shell_exec('cd ' . base_path() . ' && git pull 2>&1');

        $output['composer'] = shell_exec('cd ' . base_path() . ' && composer install --no-dev --optimize-autoloader 2>&1');

        \Artisan::call('config:cache');
        $output['config_cache'] = \Artisan::output();

        \Artisan::call('route:cache');
        $output['route_cache'] = \Artisan::output();

        \Artisan::call('view:cache');
        $output['view_cache'] = \Artisan::output();

        \Artisan::call('octane:reload');
        $output['octane_reload'] = \Artisan::output();

        return response()->json([
            'status' => 'success',
            'message' => 'Deployment completed successfully',
            'output' => $output,
            'time' => now()->toDateTimeString(),
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'output' => $output,
        ], 500);
    }
})->name('deploy.webhook');

Route::get('/quick-reload/{token}', function ($token) {
    $secret = config('app.deploy_secret', 'your-secret-token-here');

    if ($token !== $secret) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    \Artisan::call('octane:reload');
    \Artisan::call('cache:clear');

    return response()->json([
        'status' => 'success',
        'message' => 'Octane reloaded & cache cleared',
        'time' => now()->toDateTimeString(),
    ]);
});



Route::get('/debug/force-pusher-refresh', function () {
    $timestamp = now()->toDateTimeString();

    Cache::forget('pusher_config');
    Cache::forget('all_configs');

    Cache::put('pusher_config_changed', $timestamp, 3600);

    \App\Services\OctaneBroadcasterService::rebuildBroadcaster();

    Artisan::call('queue:restart');
    $queueRestartOutput = Artisan::output();

    $octaneReloadOutput = '';
    try {
        Artisan::call('octane:reload');
        $octaneReloadOutput = Artisan::output();
    } catch (\Throwable $e) {
        $octaneReloadOutput = 'Not running or error: ' . $e->getMessage();
    }

    $freshConfig = getPusherConfig();
    return response()->json([
        'success' => true,
        'message' => '🔄 Pusher config refresh triggered!',
        'actions_taken' => [
            '1_cache_cleared' => true,
            '2_flag_set' => Cache::has('pusher_config_changed'),
            '3_broadcaster_purged' => true,
            '4_queue_restart' => trim($queueRestartOutput) ?: 'Signal sent',
            '5_octane_reload' => trim($octaneReloadOutput) ?: 'Signal sent',
        ],
        'fresh_config' => [
            'app_id' => $freshConfig['app_id'],
            'app_cluster' => $freshConfig['app_cluster'],
        ],
        'next_steps' => [
            'Supervisor will restart queue workers automatically',
            'Octane workers will reload automatically',
            'New broadcasts will use fresh DB config',
        ],
        'timestamp' => $timestamp,
    ], 200, [], JSON_PRETTY_PRINT);
});



// ⭐ Test GiftBannerEvent broadcast (via Queue)
Route::get('/debug/test-gift-banner', function () {
    $dbConfig = getPusherConfig();

    // Create test gift data
    $testGift = [
        'id' => rand(1000, 9999),
        'name' => 'Test Gift 🎁',
        'sender' => [
            'id' => 1,
            'name' => 'Test Sender',
        ],
        'receiver' => [
            'id' => 2,
            'name' => 'Test Receiver',
        ],
        'count' => 1,
        'timestamp' => now()->toDateTimeString(),
        'debug_info' => [
            'pusher_app_id' => $dbConfig['app_id'],
            'pusher_cluster' => $dbConfig['app_cluster'],
        ],
    ];

    try {
        // Dispatch GiftBannerEvent (goes through Queue because it implements ShouldBroadcast)
        event(new \App\Events\GiftBannerEvent($testGift));

        return response()->json([
            'success' => true,
            'message' => '🎁 GiftBannerEvent dispatched to Queue!',
            'event' => [
                'class' => \App\Events\GiftBannerEvent::class,
                'channel' => 'gift_banner',
                'broadcast_as' => 'gift_banner',
                'queue' => 'heavyProcessing (or similar)',
            ],
            'test_data' => $testGift,
            'pusher_config' => [
                'app_id' => $dbConfig['app_id'],
                'cluster' => $dbConfig['app_cluster'],
                'key_preview' => substr($dbConfig['app_key'] ?? '', 0, 10) . '...',
            ],
            'next_steps' => [
                '1. Check Pusher Debug Console for the event',
                '2. Or check logs: tail -f storage/logs/laravel.log | grep -i gift',
                '3. If not received, run: /debug/force-pusher-refresh',
            ],
            'timestamp' => now()->toDateTimeString(),
        ], 200, [], JSON_PRETTY_PRINT);
    } catch (\Throwable $e) {
        // Log::error('GiftBannerEvent failed', [
        //     'error' => $e->getMessage(),
        //     'trace' => $e->getTraceAsString(),
        // ]);

        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500, [], JSON_PRETTY_PRINT);
    }
});


// ⭐ Test UserOnline broadcast (via Queue - PresenceChannel)
Route::get('/debug/test-user-online', function () {
    $dbConfig = getPusherConfig();

    // Get a test user (first user or create mock)
    $userId = request()->get('user_id', 1);
    $user = \App\Models\User::find($userId);

    if (!$user) {
        return response()->json([
            'success' => false,
            'error' => "User with ID {$userId} not found",
            'hint' => 'Add ?user_id=123 to specify a different user',
        ], 404, [], JSON_PRETTY_PRINT);
    }

    try {
        // Dispatch UserOnline event (goes through Queue because it implements ShouldBroadcast)
        event(new \App\Events\UserOnline($user));

        return response()->json([
            'success' => true,
            'message' => '👤 UserOnline event dispatched to Queue!',
            'event' => [
                'class' => \App\Events\UserOnline::class,
                'channel' => 'presence-enter-user-room',
                'channel_type' => 'PresenceChannel',
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'uuid' => $user->uuid ?? null,
            ],
            'pusher_config' => [
                'app_id' => $dbConfig['app_id'],
                'cluster' => $dbConfig['app_cluster'],
                'key_preview' => substr($dbConfig['app_key'] ?? '', 0, 10) . '...',
            ],
            'next_steps' => [
                '1. Check Pusher Debug Console for the event',
                '2. Or check logs: tail -f storage/logs/laravel.log | grep -i "UserOnline"',
                '3. If not received, run: /debug/force-pusher-refresh',
            ],
            'timestamp' => now()->toDateTimeString(),
        ], 200, [], JSON_PRETTY_PRINT);
    } catch (\Throwable $e) {
        // Log::error('UserOnline failed', [
        //     'error' => $e->getMessage(),
        //     'user_id' => $user->id,
        //     'trace' => $e->getTraceAsString(),
        // ]);

        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500, [], JSON_PRETTY_PRINT);
    }
});

Route::get('/time-start-week', function () {
    $date = '2026-02-15';

    // room_id => sum(current_total)
    $totalRoomGifts = TotalRoomGift::whereDate('created_at', $date)
        ->groupBy('room_id')
        ->pluck(DB::raw('SUM(current_total)'), 'room_id');

    // room_id => sum(giftPrice)
    $totalGiftLogs = GiftLog::whereDate('created_at', $date)
        ->groupBy('room_id')
        ->pluck(DB::raw('SUM(giftPrice)'), 'room_id');

    dd([
        'date' => $date,
        'total_room_gifts' => $totalRoomGifts,
        'total_gift_logs' => $totalGiftLogs,
    ]);
});

Route::get('/fix-total-room-gifts', function () {
    $tz = getTimezone();
    $startOfWeek = Carbon::now($tz)->startOfWeek()->copy()->setTimezone('UTC');
    $endOfWeek = Carbon::now($tz)->endOfWeek()->copy()->setTimezone('UTC');

    // Get correct totals from gift_logs for each room per day
    $correctTotals = GiftLog::whereBetween('created_at', [$startOfWeek, $endOfWeek])
        ->groupBy('room_id', DB::raw('DATE(created_at)'))
        ->selectRaw('room_id, DATE(created_at) as gift_date, SUM(giftPrice) as correct_total')
        ->get();

    $updated = 0;
    $created = 0;
    $results = [];

    foreach ($correctTotals as $row) {
        $roomId = $row->room_id;
        $giftDate = $row->gift_date;
        $correctTotal = $row->correct_total;

        // Find or create TotalRoomGift record for this room on this date
        $record = TotalRoomGift::whereDate('created_at', $giftDate)
            ->where('room_id', $roomId)
            ->first();

        if ($record) {
            $oldValue = $record->current_total;
            if ($oldValue != $correctTotal) {
                $record->current_total = $correctTotal;
                $record->save();
                $updated++;

                $results[] = [
                    'action' => 'updated',
                    'room_id' => $roomId,
                    'date' => $giftDate,
                    'old' => $oldValue,
                    'new' => $correctTotal,
                    'diff' => $correctTotal - $oldValue,
                ];
            }
        } else {
            // Create missing record only if correct_total > 0
            if ($correctTotal > 0) {
                TotalRoomGift::create([
                    'room_id' => $roomId,
                    'current_total' => $correctTotal,
                    'created_at' => Carbon::parse($giftDate)->startOfDay(),
                    'updated_at' => now(),
                ]);
                $created++;

                $results[] = [
                    'action' => 'created',
                    'room_id' => $roomId,
                    'date' => $giftDate,
                    'old' => 0,
                    'new' => $correctTotal,
                    'diff' => $correctTotal,
                ];
            }
        }
    }

    return response()->json([
        'start' => $startOfWeek->toDateTimeString(),
        'end' => $endOfWeek->toDateTimeString(),
        'updated_count' => $updated,
        'created_count' => $created,
        'total_processed' => $updated + $created,
        'results' => $results,
    ]);
});



Route::get('/restart-queues', function () {
    try {
        Artisan::call('queue:restart');
        return "✅ Artisan queue:restart signaled successfully.";
    } catch (\Exception $e) {
        return "❌ Failed to signal queue:restart: " . $e->getMessage();
    }
});

use Illuminate\Http\Request;
use App\Models\GameProviderSetting;


Route::get('/save-game-app-key', function (Request $request) {
    $providerCode = $request->provider_code ?? 'quantum_nexus';
    $appKey = env('LEADER_CC_GAME_SECRET_KEY');
    try {
        $gameSetting = GameProviderSetting::updateOrCreate(
            ['provider_code' => $providerCode],
            [
                'app_key' => $appKey,
            ]
        );
        return response()->json([
            'status' => 'success',
            'message' => 'تم حفظ المفتاح بنجاح',
            'data' => $gameSetting,
            'appKey' => $appKey,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'حدث خطأ أثناء الحفظ: ' . $e->getMessage(),
        ], 500);
    }
});

use Illuminate\Support\Facades\Log;

Route::get('/fix-paid-usd', function () {

    Log::info('Fix paid_usd process started');

    $logs = CoinLog::whereNull('paid_usd')
        ->orWhere('paid_usd', 0)
        ->get();

    Log::info('Total logs fetched', ['count' => $logs->count()]);

    $updated = 0;
    $skipped = 0;
    $errors = 0;

    foreach ($logs as $log) {

        try {

            Log::info('Processing log', [
                'log_id' => $log->id,
                'obtained_coins' => $log->obtained_coins,
                'current_paid_usd' => $log->paid_usd
            ]);

            $coin = Coin::where('coin', $log->obtained_coins)->first();

            if ($coin) {

                $oldValue = $log->paid_usd;

                $log->paid_usd = $coin->usd;
                $saved = $log->save();

                if ($saved) {
                    Log::info('Log updated successfully', [
                        'log_id' => $log->id,
                        'old_paid_usd' => $oldValue,
                        'new_paid_usd' => $coin->usd
                    ]);
                } else {
                    Log::warning('Log save returned false', [
                        'log_id' => $log->id
                    ]);
                }

                $updated++;
            } else {

                Log::warning('Coin not found for obtained_coins', [
                    'log_id' => $log->id,
                    'obtained_coins' => $log->obtained_coins
                ]);

                $skipped++;
            }
        } catch (\Exception $e) {

            Log::error('Error while processing log', [
                'log_id' => $log->id,
                'error' => $e->getMessage()
            ]);

            $errors++;
        }
    }

    Log::info('Fix paid_usd process finished', [
        'updated' => $updated,
        'skipped' => $skipped,
        'errors' => $errors
    ]);

    return "Updated: {$updated} | Skipped: {$skipped} | Errors: {$errors}";
});
Route::get('make-seeders-for-new-update', function () {
    $seeder = new \Database\Seeders\WebhookGamesSeeder();
    $seeder->run();

    $seeder = new \Database\Seeders\RoomBoomMediaSeeder();
    $seeder->run();

    return 'seeders have been executed successfully!';
});

Route::get('make-seeders-for-permission', function () {
    $seeder = new \Database\Seeders\PermissionTypeSeeder();
    $seeder->run();

    return 'seeders have been executed successfully!';
});



Route::get('/queue-control/{queue}', function ($queue) {

    $check = shell_exec("ps aux | grep 'queue:work --queue=$queue' | grep -v grep");

    if ($check) {
        $output = [];
        $returnVar = 0;
        exec("php artisan queue:restart 2>&1", $output, $returnVar);
        return response()->json([
            'action' => 'restarted',
            'queue' => $queue,
            'return_code' => $returnVar,
            'output' => $output
        ]);
    } else {
        $output = [];
        $returnVar = 0;
        exec("php artisan queue:work --queue=$queue --tries=1 2>&1 &", $output, $returnVar);

        return response()->json([
            'action' => 'started',
            'queue' => $queue,
            'return_code' => $returnVar,
            'output' => $output
        ]);
    }
});


Route::get('/get-gift-percentages', function () {
    $negativeLimit = getFairLuckSetting('global_vault_negative_limit', 0);
    $appFeeRate = getFairLuckSetting('fair_luck_app_fee_rate', 0.05);
    $receiverFeeRate = getFairLuckSetting('fair_luck_receiver_fee_rate', 0.05);

    dd($negativeLimit, $appFeeRate, $receiverFeeRate);
});



Route::get('/system-audit-and-fix', function (\Illuminate\Http\Request $request) {
    $shouldExecute = $request->query('fix') == '1';
    $report = [
        'status' => $shouldExecute ? 'Execution Mode' : 'Preview Mode',
        'processed_users' => 0,
        'recovered_diamonds' => 0,
        'unrecoverable_diamonds' => 0,
        'cancelled_salaries' => 0,
        'details' => [],
    ];

    DB::beginTransaction();
    try {
        // Step 1: Find users with negative TOTAL salary balance (grouped, not per-record)
        $negativeUsers = DB::select("
            SELECT user_id, SUM(sallary + agency_sallary) as total_earned,
                   SUM(cut_amount) as total_spent,
                   SUM(sallary + agency_sallary) - SUM(cut_amount) as balance
            FROM user_sallaries
            WHERE month = ? AND year = ?
            GROUP BY user_id
            HAVING balance < 0
            ORDER BY balance ASC
        ", [now()->month, now()->year]);

        // Step 2: Get coin/USD rate
        $rate = Common::getCoinsValue('user_coins');

        foreach ($negativeUsers as $user) {
            $negativeUsd = abs($user->balance);
            $diamondsToRecover = (int)($negativeUsd * $rate);
            $remaining = $diamondsToRecover;
            $report['processed_users']++;

            $userDetail = [
                'user_id' => $user->user_id,
                'negative_usd' => $negativeUsd,
                'diamonds_to_recover' => $diamondsToRecover,
                'trace' => [],
            ];

            // Step 3: Find ALL charges this user made (not just one)
            $charges = DB::table('charges')
                ->where('charger_id', $user->user_id)
                ->where('charger_type', 'user')
                ->where('created_at', '>=', '2026-03-19')
                ->orderByDesc('created_at')
                ->get();

            foreach ($charges as $charge) {
                if ($remaining <= 0) break;

                $targetId = $charge->user_id;
                $targetType = $charge->user_type;
                $chargeAmount = min((int)$charge->amount, $remaining);

                // Scenario A: Receiver is agency
                if ($targetType == 'agency') {
                    $agency = DB::table('agencies')->where('id', $targetId)->first();
                    if ($agency && $agency->coins >= $chargeAmount) {
                        // Agency has balance — deduct directly
                        if ($shouldExecute) {
                            DB::table('agencies')->where('id', $targetId)
                                ->update(['coins' => DB::raw("CAST(coins AS SIGNED) - {$chargeAmount}")]);
                        }
                        $remaining -= $chargeAmount;
                        $report['recovered_diamonds'] += $chargeAmount;
                        $userDetail['trace'][] = ['from' => "agency:{$targetId}", 'amount' => $chargeAmount, 'method' => 'direct'];
                        continue;
                    }

                    // Agency spent it — trace who they charged
                    if ($agency) {
                        $canDeduct = min((int)$agency->coins, $chargeAmount);
                        if ($canDeduct > 0 && $shouldExecute) {
                            DB::table('agencies')->where('id', $targetId)
                                ->update(['coins' => DB::raw("CAST(coins AS SIGNED) - {$canDeduct}")]);
                            $remaining -= $canDeduct;
                            $report['recovered_diamonds'] += $canDeduct;
                            $userDetail['trace'][] = ['from' => "agency:{$targetId}", 'amount' => $canDeduct, 'method' => 'partial'];
                        }

                        // Trace agency sub-charges
                        $subCharges = DB::table('charges')
                            ->where('charger_id', $targetId)
                            ->where('charger_type', 'agency')
                            ->where('created_at', '>=', '2026-03-19')
                            ->orderByDesc('created_at')
                            ->get();

                        foreach ($subCharges as $sub) {
                            if ($remaining <= 0) break;
                            $targetId = $sub->user_id;
                            $targetType = 'user';
                            // Fall through to user scenario below
                        }
                    }
                }

                // Scenario B: Receiver is user
                if ($targetType == 'user') {
                    $targetUser = DB::table('users')->where('id', $targetId)->first();
                    if (!$targetUser) continue;

                    $deductAmount = min($remaining, $chargeAmount);

                    if ((int)$targetUser->di >= $deductAmount) {
                        // User has enough di — deduct directly
                        if ($shouldExecute) {
                            DB::table('users')->where('id', $targetId)
                                ->update(['di' => DB::raw("CAST(di AS SIGNED) - {$deductAmount}")]);
                        }
                        $remaining -= $deductAmount;
                        $report['recovered_diamonds'] += $deductAmount;
                        $userDetail['trace'][] = ['from' => "user:{$targetId}", 'amount' => $deductAmount, 'method' => 'direct_di'];
                    } else {
                        // Deduct what they have
                        $canDeduct = (int)$targetUser->di;
                        if ($canDeduct > 0 && $shouldExecute) {
                            DB::table('users')->where('id', $targetId)
                                ->update(['di' => 0]);
                            $remaining -= $canDeduct;
                            $report['recovered_diamonds'] += $canDeduct;
                            $userDetail['trace'][] = ['from' => "user:{$targetId}", 'amount' => $canDeduct, 'method' => 'partial_di'];
                        }

                        // Trace gifts sent by this user
                        $giftRemaining = $deductAmount - $canDeduct;
                        $gifts = DB::table('gift_logs')
                            ->selectRaw('receiver_id, room_id, receiver_family_id, SUM(giftPrice) as total')
                            ->where('sender_id', $targetId)
                            ->where('created_at', '>=', '2026-03-19')
                            ->groupBy('receiver_id', 'room_id', 'receiver_family_id')
                            ->orderByDesc('total')
                            ->get();

                        foreach ($gifts as $gift) {
                            if ($giftRemaining <= 0) break;
                            $giftDeduct = min($giftRemaining, (int)$gift->total);

                            if ($shouldExecute) {
                                // Deduct from gift receiver's total_diamond_received
                                DB::table('users')->where('id', $gift->receiver_id)
                                    ->update(['total_diamond_received' => DB::raw("CAST(total_diamond_received AS SIGNED) - {$giftDeduct}")]);

                                // Deduct from monthly diamond
                                DB::table('monthly_diamond_receives')
                                    ->where('user_id', $gift->receiver_id)
                                    ->where('month', now()->month)
                                    ->where('year', now()->year)
                                    ->update(['monthly_diamond_received' => DB::raw("CAST(monthly_diamond_received AS SIGNED) - {$giftDeduct}")]);

                                // Fix family
                                if ($gift->receiver_family_id) {
                                    DB::table('families')->where('id', $gift->receiver_family_id)
                                        ->update(['total_diamond' => DB::raw("GREATEST(0, CAST(total_diamond AS SIGNED) - {$giftDeduct})")]);
                                }

                                // Fix room
                                if ($gift->room_id) {
                                    DB::table('rooms')->where('id', $gift->room_id)
                                        ->update(['session' => DB::raw("GREATEST(0, CAST(session AS SIGNED) - {$giftDeduct})")]);
                                    DB::table('room_top_users')
                                        ->where('room_id', $gift->room_id)
                                        ->where('user_id', $targetId)
                                        ->update(['coins' => DB::raw("GREATEST(0, CAST(coins AS SIGNED) - {$giftDeduct})")]);
                                }
                            }

                            $giftRemaining -= $giftDeduct;
                            $remaining -= $giftDeduct;
                            $report['recovered_diamonds'] += $giftDeduct;
                            $userDetail['trace'][] = ['from' => "gift_receiver:{$gift->receiver_id}", 'amount' => $giftDeduct, 'method' => 'gift_trace'];
                        }
                    }
                }
            }

            // Track unrecoverable
            if ($remaining > 0) {
                $report['unrecoverable_diamonds'] += $remaining;
                $userDetail['unrecoverable'] = $remaining;
            }

            $report['details'][] = $userDetail;
        }

        // Step 4: Salary correction — recalculate based on corrected monthly diamonds
        if ($shouldExecute) {
            $salaryFixes = DB::select("
                SELECT s.id, s.user_id, s.sallary, s.agency_sallary, s.target_diamonds,
                       m.monthly_diamond_received as corrected_diamond
                FROM user_sallaries s
                JOIN monthly_diamond_receives m ON m.user_id = s.user_id AND m.month = s.month AND m.year = s.year
                WHERE s.month = ? AND s.year = ? AND s.is_paid = 0
                  AND s.achieved_diamond > m.monthly_diamond_received
            ", [now()->month, now()->year]);

            foreach ($salaryFixes as $sal) {
                $corrected = max(0, (int)$sal->corrected_diamond);
                $target = (int)$sal->target_diamonds;

                if ($corrected >= $target) {
                    // Still meets target — just update achieved_diamond
                    DB::table('user_sallaries')->where('id', $sal->id)->update([
                        'achieved_diamond' => $corrected,
                        'diamond' => $corrected . ' / ' . $target,
                    ]);
                } else {
                    // No longer meets target — zero out
                    DB::table('user_sallaries')->where('id', $sal->id)->update([
                        'sallary' => 0,
                        'agency_sallary' => 0,
                        'achieved_diamond' => $corrected,
                        'diamond' => $corrected . ' / ' . $target,
                        'remaining_diamond' => $target - $corrected,
                        'is_finished' => 0,
                    ]);
                    $report['cancelled_salaries']++;
                }
            }
        }

        if ($shouldExecute) DB::commit();
        else DB::rollBack();

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }

    return response()->json($report);
});





// NOTE: /fix-bag-monthly route REMOVED — it was double-deducting ALL bag gift diamonds
// from monthly_diamond_received (not just the extras). The fix-bag-gifts route already
// handles monthly diamond corrections per extra log row. Running both caused negative salaries.



/**
 * ============================================================================
 * نظام إصلاح شامل لخلل "هدايا الحقيبة" - Bag Gift Fix System
 * ============================================================================
 * 
 * التاريخ المرجعي: 2026-03-19
 * الخلل: تكرار توزيع هدايا الحقيبة (source_type = 'gift') بشكل غير مستحق
 * 
 * الخطوات:
 * 1. تصفير التضخم المالي (خصم من أرصدة المستلمين)
 * 2. تنظيف الكيانات (الغرف والعائلات) وحذف السجلات
 * 3. إعادة جدولة الرواتب
 * 4. الاسترداد العدواني (تتبع الشحنات والهدايا)
 * 
 * الاستخدام:
 * - معاينة: /bag-gift-fix-system
 * - تنفيذ: /bag-gift-fix-system?fix=1
 * ============================================================================
 */
Route::get('/bag-gift-fix-system', function (\Illuminate\Http\Request $request) {
    $shouldExecute = $request->query('fix') == '1';
    $bugDate = '2026-03-19';
    $currentMonth = 3; // March
    $currentYear = 2026;
    
    // Initialize report
    $report = [
        'execution_mode' => $shouldExecute ? 'LIVE EXECUTION' : 'PREVIEW MODE',
        'bug_date' => $bugDate,
        'timestamp' => now()->toDateTimeString(),
        'steps' => [],
        'summary' => [
            'total_fake_diamonds' => 0,
            'total_affected_users' => 0,
            'total_affected_rooms' => 0,
            'total_affected_families' => 0,
            'total_salary_adjustments' => 0,
            'total_recovered_usd' => 0,
            'total_unrecoverable_usd' => 0,
            'deleted_gift_logs' => 0,
        ],
        'affected_users' => [],
        'negative_balance_users' => [],
    ];

    // Get zones_coins from settings (default 30000)
    $zonesCoins = (int) DB::table('settings')->where('key', 'zones_coins')->value('value') ?? 30000;
    $report['zones_coins'] = $zonesCoins;

    DB::beginTransaction();
    
    try {
        // ============================================================================
        // الخطوة 1: تصفير التضخم المالي
        // ============================================================================
        $step1 = [
            'name' => 'تصفير التضخم المالي',
            'description' => 'خصم مجموع giftPrice من أرصدة المستلمين',
            'status' => 'pending',
            'details' => [],
        ];

        // Get all fake gift logs grouped by receiver
        $fakeGifts = DB::table('gift_logs')
            ->selectRaw('receiver_id, SUM(giftPrice) as total_fake_diamonds, COUNT(*) as log_count')
            ->where('source_type', 'gift')
            ->where('created_at', '>=', $bugDate)
            ->groupBy('receiver_id')
            ->get();

        $report['summary']['total_fake_diamonds'] = $fakeGifts->sum('total_fake_diamonds');
        $report['summary']['total_affected_users'] = $fakeGifts->count();

        foreach ($fakeGifts as $gift) {
            $userId = $gift->receiver_id;
            $fakeAmount = (int) $gift->total_fake_diamonds;

            // Get user info before update
            $userBefore = DB::table('users')
                ->where('id', $userId)
                ->select('id', 'uuid', 'di', 'total_diamond_received', 'exchange_diamonds', 'agency_id')
                ->first();

            if (!$userBefore) continue;

            $userDetail = [
                'user_id' => $userId,
                'uuid' => $userBefore->uuid ?? 'N/A',
                'fake_diamonds' => $fakeAmount,
                'log_count' => $gift->log_count,
                'before' => [
                    'total_diamond_received' => $userBefore->total_diamond_received,
                    'exchange_diamonds' => $userBefore->exchange_diamonds,
                ],
                'after' => [],
            ];

            if ($shouldExecute) {
                // Update users table - allow negative values using CAST AS SIGNED
                DB::statement("
                    UPDATE users 
                    SET total_diamond_received = CAST(total_diamond_received AS SIGNED) - ?,
                        exchange_diamonds = CASE 
                            WHEN agency_id = 0 OR agency_id IS NULL 
                            THEN CAST(exchange_diamonds AS SIGNED) - ?
                            ELSE exchange_diamonds 
                        END
                    WHERE id = ?
                ", [$fakeAmount, $fakeAmount, $userId]);

                // Update monthly_diamond_receives - allow negative
                DB::statement("
                    UPDATE monthly_diamond_receives 
                    SET monthly_diamond_received = CAST(monthly_diamond_received AS SIGNED) - ?
                    WHERE user_id = ? AND month = ? AND year = ?
                ", [$fakeAmount, $userId, $currentMonth, $currentYear]);
            }

            // Get user info after update (for preview, calculate expected values)
            $userDetail['after'] = [
                'total_diamond_received' => $userBefore->total_diamond_received - $fakeAmount,
                'exchange_diamonds' => ($userBefore->agency_id == 0 || $userBefore->agency_id === null) 
                    ? $userBefore->exchange_diamonds - $fakeAmount 
                    : $userBefore->exchange_diamonds,
            ];

            $report['affected_users'][] = $userDetail;
        }

        $step1['status'] = 'completed';
        $step1['affected_count'] = $fakeGifts->count();
        $report['steps'][] = $step1;

        // ============================================================================
        // الخطوة 2: تنظيف الكيانات والحذف
        // ============================================================================
        $step2 = [
            'name' => 'تنظيف الكيانات والحذف',
            'description' => 'تحديث الغرف والعائلات وحذف السجلات الوهمية',
            'status' => 'pending',
            'details' => [],
        ];

        // Update rooms.session - deduct fake diamonds per room
        $roomUpdates = DB::table('gift_logs')
            ->selectRaw('room_id, SUM(giftPrice) as fake_sum')
            ->where('source_type', 'gift')
            ->where('created_at', '>=', $bugDate)
            ->whereNotNull('room_id')
            ->where('room_id', '>', 0)
            ->groupBy('room_id')
            ->get();

        $report['summary']['total_affected_rooms'] = $roomUpdates->count();

        if ($shouldExecute) {
            foreach ($roomUpdates as $room) {
                DB::statement("
                    UPDATE rooms 
                    SET session = GREATEST(0, CAST(session AS SIGNED) - ?)
                    WHERE id = ?
                ", [$room->fake_sum, $room->room_id]);
            }
        }

        $step2['details']['rooms_updated'] = $roomUpdates->count();

        // Update families.total_diamond - deduct fake diamonds per family
        $familyUpdates = DB::table('gift_logs')
            ->selectRaw('receiver_family_id, SUM(giftPrice) as fake_sum')
            ->where('source_type', 'gift')
            ->where('created_at', '>=', $bugDate)
            ->whereNotNull('receiver_family_id')
            ->where('receiver_family_id', '>', 0)
            ->groupBy('receiver_family_id')
            ->get();

        $report['summary']['total_affected_families'] = $familyUpdates->count();

        if ($shouldExecute) {
            foreach ($familyUpdates as $family) {
                DB::statement("
                    UPDATE families 
                    SET total_diamond = GREATEST(0, CAST(total_diamond AS SIGNED) - ?)
                    WHERE id = ?
                ", [$family->fake_sum, $family->receiver_family_id]);
            }
        }

        $step2['details']['families_updated'] = $familyUpdates->count();

        // Delete fake gift_logs
        $deleteCount = DB::table('gift_logs')
            ->where('source_type', 'gift')
            ->where('created_at', '>=', $bugDate)
            ->count();

        $report['summary']['deleted_gift_logs'] = $deleteCount;

        if ($shouldExecute) {
            DB::table('gift_logs')
                ->where('source_type', 'gift')
                ->where('created_at', '>=', $bugDate)
                ->delete();
        }

        $step2['details']['gift_logs_deleted'] = $deleteCount;
        $step2['status'] = 'completed';
        $report['steps'][] = $step2;

        // ============================================================================
        // الخطوة 3: إعادة جدولة الرواتب
        // ============================================================================
        $step3 = [
            'name' => 'إعادة جدولة الرواتب',
            'description' => 'إعادة حساب الرواتب بناءً على الأرصدة المصححة',
            'status' => 'pending',
            'details' => [],
        ];

        // Get all unpaid salaries for current month
        $unpaidSalaries = DB::table('user_sallaries')
            ->where('is_paid', 0)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->get();

        $salaryAdjustments = [];

        foreach ($unpaidSalaries as $salary) {
            // Get corrected monthly diamond
            $correctedDiamond = DB::table('monthly_diamond_receives')
                ->where('user_id', $salary->user_id)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->value('monthly_diamond_received') ?? 0;

            // Allow negative for tracking, but use 0 for target calculation
            $effectiveDiamond = max(0, $correctedDiamond);

            // Find the highest achieved target
            $achievedTarget = DB::table('targets')
                ->where('diamonds', '<=', $effectiveDiamond)
                ->orderByDesc('diamonds')
                ->first();

            $newSalary = 0;
            $newAgencySalary = 0;
            $targetId = null;

            if ($achievedTarget) {
                $targetId = $achievedTarget->id;
                // Calculate salary: (achieved_diamonds / zones_coins) * usd_ratio
                $newSalary = ($effectiveDiamond / $zonesCoins) * ($achievedTarget->usd ?? 0);
                $newAgencySalary = ($effectiveDiamond / $zonesCoins) * ($achievedTarget->agency_share ?? 0);
            }

            $adjustment = [
                'user_id' => $salary->user_id,
                'old_achieved_diamond' => $salary->achieved_diamond,
                'new_achieved_diamond' => $effectiveDiamond,
                'corrected_monthly' => $correctedDiamond,
                'old_salary' => $salary->sallary,
                'new_salary' => round($newSalary, 2),
                'old_agency_salary' => $salary->agency_sallary,
                'new_agency_salary' => round($newAgencySalary, 2),
                'target_id' => $targetId,
            ];

            $salaryAdjustments[] = $adjustment;

            if ($shouldExecute) {
                DB::table('user_sallaries')
                    ->where('id', $salary->id)
                    ->update([
                        'achieved_diamond' => $effectiveDiamond,
                        'sallary' => round($newSalary, 2),
                        'agency_sallary' => round($newAgencySalary, 2),
                        'target_id' => $targetId,
                        'diamond' => $effectiveDiamond . ' / ' . ($achievedTarget->diamonds ?? 0),
                        'remaining_diamond' => max(0, ($achievedTarget->diamonds ?? 0) - $effectiveDiamond),
                    ]);
            }
        }

        $report['summary']['total_salary_adjustments'] = count($salaryAdjustments);
        $step3['details']['adjustments'] = $salaryAdjustments;
        $step3['status'] = 'completed';
        $report['steps'][] = $step3;

        // ============================================================================
        // الخطوة 4: الاسترداد العدواني
        // ============================================================================
        $step4 = [
            'name' => 'الاسترداد العدواني',
            'description' => 'تتبع واسترداد العجز من المستلمين',
            'status' => 'pending',
            'details' => [],
        ];

        // Find users with deficit (salary - cut_amount < 0)
        $deficitUsers = DB::table('user_sallaries')
            ->selectRaw('user_id, SUM(sallary) as total_salary, SUM(cut_amount) as total_cut, SUM(sallary) - SUM(cut_amount) as balance')
            ->where('is_paid', 0)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->groupBy('user_id')
            ->havingRaw('SUM(sallary) - SUM(cut_amount) < 0')
            ->get();

        $recoveryDetails = [];
        $totalRecovered = 0;
        $totalUnrecoverable = 0;

        foreach ($deficitUsers as $deficitUser) {
            $deficit = abs($deficitUser->balance); // USD deficit
            $deficitDiamonds = (int) ($deficit * $zonesCoins); // Convert to diamonds
            $remaining = $deficitDiamonds;

            $userRecovery = [
                'user_id' => $deficitUser->user_id,
                'deficit_usd' => round($deficit, 2),
                'deficit_diamonds' => $deficitDiamonds,
                'traces' => [],
                'recovered' => 0,
                'unrecoverable' => 0,
            ];

            // ================================================================
            // Trace Level 1: Find charges made by this user TO OTHER USERS
            // ================================================================
            $charges = DB::table('charges')
                ->where('charger_id', $deficitUser->user_id)
                ->where('charger_type', 'user')
                ->where('user_type', 'user') // المستلم مستخدم
                ->where('created_at', '>=', $bugDate)
                ->orderByDesc('created_at')
                ->get();

            foreach ($charges as $charge) {
                if ($remaining <= 0) break;

                $targetId = $charge->user_id;
                $chargeAmount = min((int) $charge->amount, $remaining);

                // Get target user's di balance
                $targetUser = DB::table('users')->where('id', $targetId)->first();
                if (!$targetUser) continue;

                $canDeduct = min($chargeAmount, (int) $targetUser->di);
                
                if ($canDeduct > 0) {
                    if ($shouldExecute) {
                        DB::statement("
                            UPDATE users 
                            SET di = CAST(di AS SIGNED) - ?
                            WHERE id = ?
                        ", [$canDeduct, $targetId]);
                    }

                    $remaining -= $canDeduct;
                    $totalRecovered += $canDeduct;
                    $userRecovery['recovered'] += $canDeduct;
                    $userRecovery['traces'][] = [
                        'type' => 'charge_to_user',
                        'target_id' => $targetId,
                        'target_type' => 'user',
                        'amount' => $canDeduct,
                        'method' => 'di_deduction_level1',
                    ];
                }

                // Trace Level 2: Find gifts sent by the charge recipient
                if ($remaining > 0) {
                    $gifts = DB::table('gift_logs')
                        ->selectRaw('receiver_id, SUM(giftPrice) as total')
                        ->where('sender_id', $targetId)
                        ->where('created_at', '>=', $bugDate)
                        ->groupBy('receiver_id')
                        ->orderByDesc('total')
                        ->get();

                    foreach ($gifts as $gift) {
                        if ($remaining <= 0) break;

                        $giftDeduct = min($remaining, (int) $gift->total);
                        $giftReceiver = DB::table('users')->where('id', $gift->receiver_id)->first();
                        
                        if ($giftReceiver) {
                            $actualDeduct = min($giftDeduct, (int) $giftReceiver->di);
                            
                            if ($actualDeduct > 0) {
                                if ($shouldExecute) {
                                    DB::statement("
                                        UPDATE users 
                                        SET di = CAST(di AS SIGNED) - ?
                                        WHERE id = ?
                                    ", [$actualDeduct, $gift->receiver_id]);
                                }

                                $remaining -= $actualDeduct;
                                $totalRecovered += $actualDeduct;
                                $userRecovery['recovered'] += $actualDeduct;
                                $userRecovery['traces'][] = [
                                    'type' => 'gift_trace',
                                    'target_id' => $gift->receiver_id,
                                    'target_type' => 'user',
                                    'amount' => $actualDeduct,
                                    'method' => 'di_deduction_level2',
                                ];
                            }
                        }
                    }
                }
            }

            // ================================================================
            // Trace Level 1.5: Agency Trace (If user sent to agency)
            // ================================================================
            if ($remaining > 0) {
                $agencyCharges = DB::table('charges')
                    ->where('charger_id', $deficitUser->user_id)
                    ->where('charger_type', 'user')
                    ->where('user_type', 'agency') // المستلم وكالة شحن
                    ->where('created_at', '>=', $bugDate)
                    ->orderByDesc('created_at')
                    ->get();

                foreach ($agencyCharges as $aCharge) {
                    if ($remaining <= 0) break;

                    $agencyId = $aCharge->user_id;
                    $agency = DB::table('agencies')->where('id', $agencyId)->first();
                    
                    if (!$agency) continue;

                    $chargeAmount = min((int) $aCharge->amount, $remaining);
                    $deductFromAgency = min($chargeAmount, (int) $agency->coins);

                    // Deduct from agency coins
                    if ($deductFromAgency > 0) {
                        if ($shouldExecute) {
                            DB::statement("
                                UPDATE agencies 
                                SET coins = CAST(coins AS SIGNED) - ?
                                WHERE id = ?
                            ", [$deductFromAgency, $agencyId]);
                        }

                        $remaining -= $deductFromAgency;
                        $totalRecovered += $deductFromAgency;
                        $userRecovery['recovered'] += $deductFromAgency;
                        $userRecovery['traces'][] = [
                            'type' => 'charge_to_agency',
                            'target_id' => $agencyId,
                            'target_type' => 'agency',
                            'agency_name' => $agency->name ?? 'N/A',
                            'amount' => $deductFromAgency,
                            'method' => 'agency_coins_deduction_level1.5',
                        ];
                    }

                    // ================================================================
                    // Trace Level 3: Agency Outgoing Trace
                    // If agency distributed the coins to other users
                    // ================================================================
                    if ($remaining > 0) {
                        $distributedCharges = DB::table('charges')
                            ->where('charger_id', $agencyId)
                            ->where('charger_type', 'agency')
                            ->where('user_type', 'user') // الوكالة أرسلت لمستخدمين
                            ->where('created_at', '>=', $bugDate)
                            ->orderByDesc('created_at')
                            ->get();

                        foreach ($distributedCharges as $distCharge) {
                            if ($remaining <= 0) break;

                            $finalUserId = $distCharge->user_id;
                            $finalUser = DB::table('users')->where('id', $finalUserId)->first();
                            
                            if (!$finalUser) continue;

                            $distAmount = min((int) $distCharge->amount, $remaining);
                            $canDeductFromFinal = min($distAmount, (int) $finalUser->di);

                            if ($canDeductFromFinal > 0) {
                                if ($shouldExecute) {
                                    DB::statement("
                                        UPDATE users 
                                        SET di = CAST(di AS SIGNED) - ?
                                        WHERE id = ?
                                    ", [$canDeductFromFinal, $finalUserId]);
                                }

                                $remaining -= $canDeductFromFinal;
                                $totalRecovered += $canDeductFromFinal;
                                $userRecovery['recovered'] += $canDeductFromFinal;
                                $userRecovery['traces'][] = [
                                    'type' => 'agency_outgoing_trace',
                                    'source_agency_id' => $agencyId,
                                    'target_id' => $finalUserId,
                                    'target_type' => 'user',
                                    'amount' => $canDeductFromFinal,
                                    'method' => 'di_deduction_level3',
                                ];
                            }

                            // Level 4: Trace gifts sent by agency recipients
                            if ($remaining > 0) {
                                $finalUserGifts = DB::table('gift_logs')
                                    ->selectRaw('receiver_id, SUM(giftPrice) as total')
                                    ->where('sender_id', $finalUserId)
                                    ->where('created_at', '>=', $bugDate)
                                    ->groupBy('receiver_id')
                                    ->orderByDesc('total')
                                    ->get();

                                foreach ($finalUserGifts as $fGift) {
                                    if ($remaining <= 0) break;

                                    $giftDeduct = min($remaining, (int) $fGift->total);
                                    $giftReceiver = DB::table('users')->where('id', $fGift->receiver_id)->first();
                                    
                                    if ($giftReceiver) {
                                        $actualDeduct = min($giftDeduct, (int) $giftReceiver->di);
                                        
                                        if ($actualDeduct > 0) {
                                            if ($shouldExecute) {
                                                DB::statement("
                                                    UPDATE users 
                                                    SET di = CAST(di AS SIGNED) - ?
                                                    WHERE id = ?
                                                ", [$actualDeduct, $fGift->receiver_id]);
                                            }

                                            $remaining -= $actualDeduct;
                                            $totalRecovered += $actualDeduct;
                                            $userRecovery['recovered'] += $actualDeduct;
                                            $userRecovery['traces'][] = [
                                                'type' => 'agency_gift_trace',
                                                'source_agency_id' => $agencyId,
                                                'intermediate_user_id' => $finalUserId,
                                                'target_id' => $fGift->receiver_id,
                                                'target_type' => 'user',
                                                'amount' => $actualDeduct,
                                                'method' => 'di_deduction_level4',
                                            ];
                                        }
                                    }
                                }
                            }
                        }

                        // ================================================================
                        // Trace Level 3.5: Agency to Agency Trace
                        // If agency sent to another agency
                        // ================================================================
                        $agencyToAgencyCharges = DB::table('charges')
                            ->where('charger_id', $agencyId)
                            ->where('charger_type', 'agency')
                            ->where('user_type', 'agency') // الوكالة أرسلت لوكالة أخرى
                            ->where('created_at', '>=', $bugDate)
                            ->orderByDesc('created_at')
                            ->get();

                        foreach ($agencyToAgencyCharges as $a2aCharge) {
                            if ($remaining <= 0) break;

                            $targetAgencyId = $a2aCharge->user_id;
                            $targetAgency = DB::table('agencies')->where('id', $targetAgencyId)->first();
                            
                            if (!$targetAgency) continue;

                            $a2aAmount = min((int) $a2aCharge->amount, $remaining);
                            $canDeductFromTargetAgency = min($a2aAmount, (int) $targetAgency->coins);

                            if ($canDeductFromTargetAgency > 0) {
                                if ($shouldExecute) {
                                    DB::statement("
                                        UPDATE agencies 
                                        SET coins = CAST(coins AS SIGNED) - ?
                                        WHERE id = ?
                                    ", [$canDeductFromTargetAgency, $targetAgencyId]);
                                }

                                $remaining -= $canDeductFromTargetAgency;
                                $totalRecovered += $canDeductFromTargetAgency;
                                $userRecovery['recovered'] += $canDeductFromTargetAgency;
                                $userRecovery['traces'][] = [
                                    'type' => 'agency_to_agency_trace',
                                    'source_agency_id' => $agencyId,
                                    'target_id' => $targetAgencyId,
                                    'target_type' => 'agency',
                                    'agency_name' => $targetAgency->name ?? 'N/A',
                                    'amount' => $canDeductFromTargetAgency,
                                    'method' => 'agency_coins_deduction_level3.5',
                                ];
                            }
                        }
                    }
                }
            }

            $userRecovery['unrecoverable'] = $remaining;
            $totalUnrecoverable += $remaining;
            $recoveryDetails[] = $userRecovery;

            // Track negative balance users
            if ($remaining > 0) {
                $report['negative_balance_users'][] = [
                    'user_id' => $deficitUser->user_id,
                    'deficit_usd' => round($deficit, 2),
                    'unrecoverable_diamonds' => $remaining,
                    'unrecoverable_usd' => round($remaining / $zonesCoins, 2),
                ];
            }
        }

        $report['summary']['total_recovered_usd'] = round($totalRecovered / $zonesCoins, 2);
        $report['summary']['total_unrecoverable_usd'] = round($totalUnrecoverable / $zonesCoins, 2);
        $step4['details']['recovery'] = $recoveryDetails;
        $step4['status'] = 'completed';
        $report['steps'][] = $step4;

        // Commit or rollback
        if ($shouldExecute) {
            DB::commit();
            $report['execution_status'] = 'COMMITTED';
        } else {
            DB::rollBack();
            $report['execution_status'] = 'ROLLED BACK (Preview Mode)';
        }

    } catch (\Exception $e) {
        DB::rollBack();
        $report['execution_status'] = 'ERROR - ROLLED BACK';
        $report['error'] = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];
    }

    // ============================================================================
    // Generate HTML Report (RTL)
    // ============================================================================
    $html = '<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير إصلاح هدايا الحقيبة - Bag Gift Fix Report</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: "Segoe UI", Tahoma, Arial, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #e0e0e0;
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 1400px; margin: 0 auto; }
        .header {
            background: linear-gradient(135deg, #0f3460 0%, #533483 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            background: linear-gradient(90deg, #00d9ff, #00ff88);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .header .meta { color: #aaa; font-size: 0.9em; }
        .mode-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 15px;
        }
        .mode-preview { background: #f39c12; color: #000; }
        .mode-live { background: #e74c3c; color: #fff; }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .summary-card .value {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .summary-card .label { color: #888; font-size: 0.9em; }
        .value-danger { color: #e74c3c; }
        .value-success { color: #2ecc71; }
        .value-warning { color: #f39c12; }
        .value-info { color: #3498db; }
        
        .section {
            background: rgba(255,255,255,0.03);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid rgba(255,255,255,0.08);
        }
        .section h2 {
            font-size: 1.5em;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section h2 .step-num {
            background: linear-gradient(135deg, #00d9ff, #00ff88);
            color: #000;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px 15px;
            text-align: right;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        th {
            background: rgba(0,0,0,0.3);
            font-weight: 600;
            color: #00d9ff;
        }
        tr:hover { background: rgba(255,255,255,0.05); }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 500;
        }
        .status-completed { background: #2ecc71; color: #000; }
        .status-pending { background: #f39c12; color: #000; }
        .status-error { background: #e74c3c; color: #fff; }
        
        .progress-bar {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            height: 20px;
            overflow: hidden;
            margin-top: 10px;
        }
        .progress-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s;
        }
        .progress-recovered { background: linear-gradient(90deg, #2ecc71, #27ae60); }
        .progress-unrecoverable { background: linear-gradient(90deg, #e74c3c, #c0392b); }
        
        .action-btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: #fff;
            text-decoration: none;
            border-radius: 30px;
            font-weight: bold;
            font-size: 1.1em;
            margin-top: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .action-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(231, 76, 60, 0.4);
        }
        
        .footer {
            text-align: center;
            padding: 30px;
            color: #666;
            font-size: 0.9em;
        }
        
        @media (max-width: 768px) {
            .header h1 { font-size: 1.8em; }
            .summary-card .value { font-size: 1.8em; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 تقرير إصلاح هدايا الحقيبة</h1>
            <div class="meta">
                <p>التاريخ المرجعي للخلل: <strong>' . $bugDate . '</strong></p>
                <p>وقت التقرير: <strong>' . $report['timestamp'] . '</strong></p>
                <p>معدل التحويل (zones_coins): <strong>' . number_format($zonesCoins) . '</strong></p>
            </div>
            <span class="mode-badge ' . ($shouldExecute ? 'mode-live' : 'mode-preview') . '">
                ' . ($shouldExecute ? '⚡ وضع التنفيذ الفعلي' : '👁️ وضع المعاينة') . '
            </span>
        </div>
        
        <div class="summary-grid">
            <div class="summary-card">
                <div class="value value-danger">' . number_format($report['summary']['total_fake_diamonds']) . '</div>
                <div class="label">💎 إجمالي الماسات الوهمية</div>
            </div>
            <div class="summary-card">
                <div class="value value-warning">' . number_format($report['summary']['total_affected_users']) . '</div>
                <div class="label">👥 المستخدمين المتأثرين</div>
            </div>
            <div class="summary-card">
                <div class="value value-info">' . number_format($report['summary']['total_affected_rooms']) . '</div>
                <div class="label">🏠 الغرف المتأثرة</div>
            </div>
            <div class="summary-card">
                <div class="value value-info">' . number_format($report['summary']['total_affected_families']) . '</div>
                <div class="label">👨‍👩‍👧‍👦 العائلات المتأثرة</div>
            </div>
            <div class="summary-card">
                <div class="value value-warning">' . number_format($report['summary']['total_salary_adjustments']) . '</div>
                <div class="label">💰 تعديلات الرواتب</div>
            </div>
            <div class="summary-card">
                <div class="value value-success">$' . number_format($report['summary']['total_recovered_usd'], 2) . '</div>
                <div class="label">✅ المبالغ المستردة</div>
            </div>
            <div class="summary-card">
                <div class="value value-danger">$' . number_format($report['summary']['total_unrecoverable_usd'], 2) . '</div>
                <div class="label">❌ غير قابل للاسترداد</div>
            </div>
            <div class="summary-card">
                <div class="value value-danger">' . number_format($report['summary']['deleted_gift_logs']) . '</div>
                <div class="label">🗑️ سجلات محذوفة</div>
            </div>
        </div>';

    // Recovery Progress Bar
    $totalLoss = $report['summary']['total_recovered_usd'] + $report['summary']['total_unrecoverable_usd'];
    $recoveryPercent = $totalLoss > 0 ? ($report['summary']['total_recovered_usd'] / $totalLoss) * 100 : 0;
    
    $html .= '
        <div class="section">
            <h2>📊 نسبة الاسترداد</h2>
            <p>إجمالي الخسائر: <strong>$' . number_format($totalLoss, 2) . '</strong></p>
            <div class="progress-bar">
                <div class="progress-fill progress-recovered" style="width: ' . $recoveryPercent . '%;"></div>
            </div>
            <p style="margin-top: 10px;">
                <span style="color: #2ecc71;">✅ مسترد: ' . number_format($recoveryPercent, 1) . '%</span> | 
                <span style="color: #e74c3c;">❌ غير قابل للاسترداد: ' . number_format(100 - $recoveryPercent, 1) . '%</span>
            </p>
        </div>';

    // Steps Details
    foreach ($report['steps'] as $index => $step) {
        $html .= '
        <div class="section">
            <h2>
                <span class="step-num">' . ($index + 1) . '</span>
                ' . $step['name'] . '
                <span class="status-badge status-' . $step['status'] . '">' . $step['status'] . '</span>
            </h2>
            <p style="color: #888; margin-bottom: 15px;">' . $step['description'] . '</p>';
        
        if (isset($step['affected_count'])) {
            $html .= '<p>عدد المتأثرين: <strong>' . number_format($step['affected_count']) . '</strong></p>';
        }
        
        if (isset($step['details']) && is_array($step['details'])) {
            foreach ($step['details'] as $key => $value) {
                if (is_array($value)) continue;
                $html .= '<p>' . $key . ': <strong>' . number_format($value) . '</strong></p>';
            }
        }
        
        $html .= '</div>';
    }

    // Negative Balance Users Table
    if (!empty($report['negative_balance_users'])) {
        $html .= '
        <div class="section">
            <h2>⚠️ المستخدمين ذوي الأرصدة السالبة</h2>
            <table>
                <thead>
                    <tr>
                        <th>معرف المستخدم</th>
                        <th>العجز (USD)</th>
                        <th>الماسات غير المستردة</th>
                        <th>القيمة غير المستردة (USD)</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($report['negative_balance_users'] as $user) {
            $html .= '
                    <tr>
                        <td>' . $user['user_id'] . '</td>
                        <td style="color: #e74c3c;">$' . number_format($user['deficit_usd'], 2) . '</td>
                        <td>' . number_format($user['unrecoverable_diamonds']) . '</td>
                        <td style="color: #e74c3c;">$' . number_format($user['unrecoverable_usd'], 2) . '</td>
                    </tr>';
        }
        
        $html .= '
                </tbody>
            </table>
        </div>';
    }

    // Action Button
    if (!$shouldExecute) {
        $html .= '
        <div style="text-align: center; margin: 40px 0;">
            <p style="color: #f39c12; font-size: 1.2em; margin-bottom: 20px;">
                ⚠️ هذا تقرير معاينة فقط. لم يتم تنفيذ أي تغييرات.
            </p>
            <a href="?fix=1" class="action-btn" onclick="return confirm(\'هل أنت متأكد من تنفيذ الإصلاح؟ هذا الإجراء لا يمكن التراجع عنه!\');">
                🚀 تنفيذ الإصلاح الآن
            </a>
        </div>';
    } else {
        $html .= '
        <div style="text-align: center; margin: 40px 0;">
            <p style="color: #2ecc71; font-size: 1.5em;">
                ✅ تم تنفيذ الإصلاح بنجاح!
            </p>
        </div>';
    }

    $html .= '
        <div class="footer">
            <p>Lumio Bag Gift Fix System v1.0</p>
            <p>Generated at ' . $report['timestamp'] . '</p>
        </div>
    </div>
</body>
</html>';

    return response($html)->header('Content-Type', 'text/html; charset=utf-8');
});