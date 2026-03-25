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

Route::get('/fix-bag-gifts', function (\Illuminate\Http\Request $request) {
    // Find bugged bag gift batches: source_type='gift' sent to multiple receivers after the bug date
    $affected = DB::table('gift_logs')
        ->selectRaw('sender_id, giftId, giftPrice, giftNum, created_at, COUNT(*) as receiver_count')
        ->where('source_type', 'gift')
        ->where('created_at', '>=', '2026-03-19')
        ->groupBy('sender_id', 'giftId', 'giftPrice', 'giftNum', 'created_at')
        ->havingRaw('COUNT(*) > 1')
        ->get();

    Log::info("Found " . $affected->count() . " affected bag gift transactions.");

    if ($affected->isEmpty()) {
        return response()->json([
            'status' => 'ok',
            'message' => 'No affected bag gift transactions found.',
        ]);
    }

    $shouldExecute = $request->query('fix') == '1';
    $totalExcess = 0;
    $details = [];

    DB::transaction(function () use ($affected, $shouldExecute, &$totalExcess, &$details) {
        foreach ($affected as $group) {
            $logs = DB::table('gift_logs')
                ->where('sender_id', $group->sender_id)
                ->where('giftId', $group->giftId)
                ->where('giftPrice', $group->giftPrice)
                ->where('giftNum', $group->giftNum)
                ->where('created_at', $group->created_at)
                ->where('source_type', 'gift')
                ->orderBy('id')
                ->get(['id', 'receiver_id', 'giftPrice', 'created_at', 'room_id', 'receiver_family_id']);

            $extraLogs = $logs->slice(1);
            $excessDiamonds = (int) $extraLogs->sum('giftPrice');
            $totalExcess += $excessDiamonds;

            $details[] = [
                'sender_id' => $group->sender_id,
                'gift_id' => $group->giftId,
                'created_at' => $group->created_at,
                'gift_price_per_receiver' => (int) $group->giftPrice,
                'total_receivers' => $group->receiver_count,
                'excess_diamonds' => $excessDiamonds,
                'kept_receiver' => $logs->first()->receiver_id,
                'extra_receivers' => $extraLogs->pluck('receiver_id')->values()->toArray(),
            ];

            if ($shouldExecute) {
                // Reverse diamonds for each extra receiver
                foreach ($extraLogs as $log) {
                    $logPrice = (int) $log->giftPrice;

                    // Reverse total_diamond_received
                    DB::table('users')
                        ->where('id', $log->receiver_id)
                        ->update([
                            'total_diamond_received' => DB::raw("GREATEST(0, CAST(total_diamond_received AS SIGNED) - {$logPrice})"),
                        ]);

                    // Reverse exchange_diamonds (only for non-agency users)
                    DB::table('users')
                        ->where('id', $log->receiver_id)
                        ->where('agency_id', 0)
                        ->update([
                            'exchange_diamonds' => DB::raw("GREATEST(0, CAST(exchange_diamonds AS SIGNED) - {$logPrice})"),
                        ]);

                    // Reverse monthly_diamond_received
                    $logDate = \Carbon\Carbon::parse($log->created_at, getTimezone());
                    DB::table('monthly_diamond_receives')
                        ->where('user_id', $log->receiver_id)
                        ->where('month', $logDate->month)
                        ->where('year', $logDate->year)
                        ->update([
                            'monthly_diamond_received' => DB::raw("GREATEST(0, CAST(monthly_diamond_received AS SIGNED) - {$logPrice})"),
                        ]);
                }

                // NOTE: Sender refund intentionally skipped — senders already spent their diamonds
                // and the app has already collected those coins. No refund needed.

                // Fix room session (was inflated by excess)
                $roomId = $logs->first()->room_id;
                if ($roomId) {
                    DB::table('rooms')
                        ->where('id', $roomId)
                        ->update([
                            'session' => DB::raw("GREATEST(0, CAST(session AS SIGNED) - {$excessDiamonds})"),
                        ]);
                }

                // Fix room_top_users (sender coins were inflated)
                if ($roomId) {
                    DB::table('room_top_users')
                        ->where('room_id', $roomId)
                        ->where('user_id', $group->sender_id)
                        ->update([
                            'coins' => DB::raw("GREATEST(0, CAST(coins AS SIGNED) - {$excessDiamonds})"),
                        ]);
                }

                // Fix total_room_gifts (room boom totals were inflated)
                if ($roomId) {
                    $logDate = \Carbon\Carbon::parse($logs->first()->created_at, getTimezone());
                    DB::table('total_room_gifts')
                        ->where('room_id', $roomId)
                        ->whereDate('created_at', $logDate->toDateString())
                        ->update([
                            'current_total' => DB::raw("GREATEST(0, CAST(current_total AS SIGNED) - {$excessDiamonds})"),
                        ]);
                }

                // Fix family total_diamond for extra receivers' families
                $familyIds = $extraLogs->pluck('receiver_family_id')->filter()->unique();
                foreach ($familyIds as $familyId) {
                    $familyExcess = (int) $extraLogs->where('receiver_family_id', $familyId)->sum('giftPrice');
                    DB::table('families')
                        ->where('id', $familyId)
                        ->update([
                            'total_diamond' => DB::raw("GREATEST(0, CAST(total_diamond AS SIGNED) - {$familyExcess})"),
                        ]);
                }

                // Delete the extra (exploit) gift_log records
                $extraIds = $extraLogs->pluck('id')->toArray();
                DB::table('gift_logs')->whereIn('id', $extraIds)->delete();
            }
        }

        // Fix salaries: zero out any salary where corrected monthly_diamond no longer meets target
        if ($shouldExecute) {
            $salaryFixes = DB::select("
                SELECT s.id, s.user_id, s.sallary, s.agency_sallary, s.achieved_diamond, s.target_diamonds, m.monthly_diamond_received
                FROM user_sallaries s
                JOIN monthly_diamond_receives m ON m.user_id = s.user_id AND m.month = s.month AND m.year = s.year
                WHERE s.month = ? AND s.year = ? AND s.is_paid = 0
                  AND m.monthly_diamond_received < s.target_diamonds
                  AND s.achieved_diamond > m.monthly_diamond_received
            ", [now()->month, now()->year]);

            foreach ($salaryFixes as $sal) {
                DB::table('user_sallaries')
                    ->where('id', $sal->id)
                    ->update([
                        'achieved_diamond' => $sal->monthly_diamond_received,
                        'sallary' => 0,
                        'agency_sallary' => 0,
                        'diamond' => $sal->monthly_diamond_received . ' / ' . $sal->target_diamonds,
                        'remaining_diamond' => max(0, $sal->target_diamonds - $sal->monthly_diamond_received),
                        'is_finished' => 0,
                    ]);
            }
        }
    });

    $salaryReport = DB::table('user_sallaries as s')
        ->join('monthly_diamond_receives as m', function ($join) {
            $join->on('m.user_id', '=', 's.user_id')
                ->where('m.month', '=', DB::raw('s.month'))
                ->where('m.year', '=', DB::raw('s.year'));
        })
        ->where('s.month', now()->month)
        ->where('s.year', now()->year)
        ->where('s.is_paid', 0)
        ->whereColumn('s.achieved_diamond', '>', 'm.monthly_diamond_received')
        ->select('s.user_id', 's.achieved_diamond', 'm.monthly_diamond_received', 's.target_diamonds', 's.sallary', 's.agency_sallary')
        ->get();

    return response()->json([
        'status' => $shouldExecute ? 'fixed' : 'report',
        'total_affected_transactions' => $affected->count(),
        'total_excess_diamonds' => $totalExcess,
        'salary_corrections' => $salaryReport->count(),
        'details' => $details,
    ]);
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



Route::get('/direct-recovery', function (Request $request) {
    $isLive = $request->query('fix') == '1';
    $zonesCoins = (int) DB::table('settings')->where('key', 'zones_coins')->value('value') ?? 30000;
    $bugDate = '2026-03-01';
    $targetMonth = 3;
    $targetYear = 2026;

    $report = [
        'mode' => $isLive ? 'LIVE EXECUTION' : 'PREVIEW MODE',
        'timestamp' => now()->toDateTimeString(),
        'zones_coins' => $zonesCoins,
        'bug_date' => $bugDate,
        'summary' => [
            'total_debtors' => 0,
            'total_debt_usd' => 0,
            'total_debt_coins' => 0,
            'total_recovered_coins' => 0,
            'total_unrecoverable_coins' => 0,
            'agencies_affected' => 0,
            'users_affected' => 0,
        ],
        'debtors' => [],
        'errors' => [],
    ];

    DB::beginTransaction();

    try {
        // ================================================================
        // Step 1: Fetch all debtors (users with negative salary balance)
        // ================================================================
        $debtors = DB::select("
            SELECT user_id,
                   SUM(sallary) as total_earned,
                   SUM(cut_amount) as total_cut,
                   SUM(sallary) - SUM(cut_amount) as total_debt
            FROM user_sallaries
            WHERE month = ? AND year = ?
            GROUP BY user_id
            HAVING total_debt < 0
            ORDER BY total_debt ASC
        ", [$targetMonth, $targetYear]);

        $report['summary']['total_debtors'] = count($debtors);

        // ================================================================
        // Step 2: Process each debtor
        // ================================================================
        foreach ($debtors as $debtor) {
            $debtUsd = abs($debtor->total_debt);
            $debtCoins = (int) ($debtUsd * $zonesCoins);
            $remaining = $debtCoins;

            $debtorDetail = [
                'user_id' => $debtor->user_id,
                'total_earned_usd' => round($debtor->total_earned, 2),
                'total_cut_usd' => round($debtor->total_cut, 2),
                'debt_usd' => round($debtUsd, 2),
                'debt_coins' => $debtCoins,
                'traces' => [],
                'recovered_coins' => 0,
                'unrecoverable_coins' => 0,
                'status' => 'pending',
            ];

            $report['summary']['total_debt_usd'] += $debtUsd;
            $report['summary']['total_debt_coins'] += $debtCoins;

            // Get user info
            $user = DB::table('users')->where('id', $debtor->user_id)->first();
            $debtorDetail['user_name'] = $user->name ?? 'N/A';
            $debtorDetail['user_uuid'] = $user->uuid ?? 'N/A';

            // ================================================================
            // Scenario A: Trace charges to agencies
            // ================================================================
            $agencyCharges = DB::table('charges')
                ->where('charger_id', $debtor->user_id)
                ->where('charger_type', 'user')
                ->where('user_type', 'agency')
                ->where('created_at', '>=', $bugDate)
                ->orderByDesc('amount')
                ->get();

            foreach ($agencyCharges as $charge) {
                if ($remaining <= 0) break;

                $agencyId = $charge->user_id;
                $chargeAmount = (int) $charge->amount;
                $deductAmount = min($remaining, $chargeAmount);

                // Get agency info
                $agency = DB::table('agencies')->where('id', $agencyId)->first();
                if (!$agency) continue;

                $agencyCoins = (int) $agency->coins;
                $canDeduct = min($deductAmount, $agencyCoins);

                $trace = [
                    'type' => 'agency_charge',
                    'target_id' => $agencyId,
                    'target_name' => $agency->name ?? 'N/A',
                    'charge_amount' => $chargeAmount,
                    'requested_deduction' => $deductAmount,
                    'agency_balance_before' => $agencyCoins,
                    'deducted_amount' => $canDeduct,
                    'agency_balance_after' => $agencyCoins - $canDeduct,
                    'status' => $canDeduct >= $deductAmount ? 'success' : 'insufficient_balance',
                ];

                if ($isLive && $canDeduct > 0) {
                    DB::statement("
                        UPDATE agencies
                        SET coins = CAST(coins AS SIGNED) - ?
                        WHERE id = ?
                    ", [$canDeduct, $agencyId]);
                }

                $remaining -= $canDeduct;
                $debtorDetail['recovered_coins'] += $canDeduct;
                $debtorDetail['traces'][] = $trace;

                if ($canDeduct < $deductAmount) {
                    $report['summary']['agencies_affected']++;
                }
            }

            // ================================================================
            // Scenario B: Trace gifts to users
            // ================================================================
            if ($remaining > 0) {
                $gifts = DB::select("
                    SELECT receiver_id, SUM(giftPrice) as total_sent
                    FROM gift_logs
                    WHERE sender_id = ? AND created_at >= ? AND created_at < '2026-04-01'
                    GROUP BY receiver_id
                    ORDER BY total_sent DESC
                    LIMIT 50
                ", [$debtor->user_id, $bugDate]);

                foreach ($gifts as $gift) {
                    if ($remaining <= 0) break;

                    $receiverId = $gift->receiver_id;
                    $giftAmount = (int) $gift->total_sent;
                    $deductAmount = min($remaining, $giftAmount);

                    // Get receiver info
                    $receiver = DB::table('users')->where('id', $receiverId)->first();
                    if (!$receiver) continue;

                    $receiverDi = (int) $receiver->di;
                    $canDeduct = min($deductAmount, $receiverDi);

                    $trace = [
                        'type' => 'gift_to_user',
                        'target_id' => $receiverId,
                        'target_name' => $receiver->name ?? 'N/A',
                        'gift_amount' => $giftAmount,
                        'requested_deduction' => $deductAmount,
                        'receiver_di_before' => $receiverDi,
                        'deducted_amount' => $canDeduct,
                        'receiver_di_after' => $receiverDi - $canDeduct,
                        'status' => $canDeduct >= $deductAmount ? 'success' : 'insufficient_balance',
                    ];

                    if ($isLive && $canDeduct > 0) {
                        DB::statement("
                            UPDATE users
                            SET di = CAST(di AS SIGNED) - ?
                            WHERE id = ?
                        ", [$canDeduct, $receiverId]);
                    }

                    $remaining -= $canDeduct;
                    $debtorDetail['recovered_coins'] += $canDeduct;
                    $debtorDetail['traces'][] = $trace;

                    if ($canDeduct < $deductAmount) {
                        $report['summary']['users_affected']++;
                    }
                }
            }

            // ================================================================
            // Scenario C: Trace charges to other users
            // ================================================================
            if ($remaining > 0) {
                $userCharges = DB::table('charges')
                    ->where('charger_id', $debtor->user_id)
                    ->where('charger_type', 'user')
                    ->where('user_type', 'user')
                    ->where('created_at', '>=', $bugDate)
                    ->orderByDesc('amount')
                    ->get();

                foreach ($userCharges as $charge) {
                    if ($remaining <= 0) break;

                    $chargeRecipientId = $charge->user_id;
                    $chargeAmount = (int) $charge->amount;
                    $deductAmount = min($remaining, $chargeAmount);

                    // Get recipient info
                    $recipient = DB::table('users')->where('id', $chargeRecipientId)->first();
                    if (!$recipient) continue;

                    $recipientDi = (int) $recipient->di;
                    $canDeduct = min($deductAmount, $recipientDi);

                    $trace = [
                        'type' => 'charge_to_user',
                        'target_id' => $chargeRecipientId,
                        'target_name' => $recipient->name ?? 'N/A',
                        'charge_amount' => $chargeAmount,
                        'requested_deduction' => $deductAmount,
                        'recipient_di_before' => $recipientDi,
                        'deducted_amount' => $canDeduct,
                        'recipient_di_after' => $recipientDi - $canDeduct,
                        'status' => $canDeduct >= $deductAmount ? 'success' : 'insufficient_balance',
                    ];

                    if ($isLive && $canDeduct > 0) {
                        DB::statement("
                            UPDATE users
                            SET di = CAST(di AS SIGNED) - ?
                            WHERE id = ?
                        ", [$canDeduct, $chargeRecipientId]);
                    }

                    $remaining -= $canDeduct;
                    $debtorDetail['recovered_coins'] += $canDeduct;
                    $debtorDetail['traces'][] = $trace;

                    if ($canDeduct < $deductAmount) {
                        $report['summary']['users_affected']++;
                    }
                }
            }

            // ================================================================
            // Final Status
            // ================================================================
            $debtorDetail['unrecoverable_coins'] = $remaining;
            $debtorDetail['recovery_rate'] = $debtCoins > 0
                ? round(($debtorDetail['recovered_coins'] / $debtCoins) * 100, 2)
                : 0;
            $debtorDetail['status'] = $remaining <= 0 ? 'fully_recovered' : 'partially_recovered';

            $report['summary']['total_recovered_coins'] += $debtorDetail['recovered_coins'];
            $report['summary']['total_unrecoverable_coins'] += $remaining;

            $report['debtors'][] = $debtorDetail;
        }

        // Commit or rollback
        if ($isLive) {
            DB::commit();
            $report['execution_status'] = 'COMMITTED';
        } else {
            DB::rollBack();
            $report['execution_status'] = 'ROLLED BACK (Preview Mode)';
        }

    } catch (\Exception $e) {
        DB::rollBack();
        $report['execution_status'] = 'ERROR - ROLLED BACK';
        $report['errors'][] = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];
    }

    // ================================================================
    // Generate HTML Report (RTL)
    // ================================================================
    $html = '<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام الاسترجاع المباشر - Direct Recovery System</title>
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
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 0.9em;
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
        .status-success { background: #2ecc71; color: #000; }
        .status-partial { background: #f39c12; color: #000; }
        .status-insufficient { background: #e74c3c; color: #fff; }

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
            margin-left: 10px;
        }
        .action-btn:hover {
            transform: scale(1.05);
        }

        .download-btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: #fff;
            text-decoration: none;
            border-radius: 30px;
            font-weight: bold;
            font-size: 1.1em;
            margin-top: 20px;
        }

        .footer {
            text-align: center;
            padding: 30px;
            color: #666;
            font-size: 0.9em;
        }

        .trace-item {
            background: rgba(255,255,255,0.02);
            border-right: 3px solid #00d9ff;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
        }

        .trace-item .type { color: #00d9ff; font-weight: bold; }
        .trace-item .status { margin-top: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 نظام الاسترجاع المباشر</h1>
            <div class="meta">
                <p>الفترة المستهدفة: <strong>' . $bugDate . ' إلى 2026-03-31</strong></p>
                <p>وقت التقرير: <strong>' . $report['timestamp'] . '</strong></p>
                <p>معدل التحويل (zones_coins): <strong>' . number_format($zonesCoins) . '</strong></p>
            </div>
            <span class="mode-badge ' . ($isLive ? 'mode-live' : 'mode-preview') . '">
                ' . ($isLive ? '⚡ وضع التنفيذ الفعلي' : '👁️ وضع المعاينة') . '
            </span>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="value value-warning">' . number_format($report['summary']['total_debtors']) . '</div>
                <div class="label">👥 إجمالي المدينين</div>
            </div>
            <div class="summary-card">
                <div class="value value-danger">$' . number_format($report['summary']['total_debt_usd'], 2) . '</div>
                <div class="label">💸 إجمالي الديون (USD)</div>
            </div>
            <div class="summary-card">
                <div class="value value-success">' . number_format($report['summary']['total_recovered_coins']) . '</div>
                <div class="label">✅ الماسات المستردة</div>
            </div>
            <div class="summary-card">
                <div class="value value-danger">' . number_format($report['summary']['total_unrecoverable_coins']) . '</div>
                <div class="label">❌ الماسات غير المستردة</div>
            </div>
            <div class="summary-card">
                <div class="value value-info">' . number_format($report['summary']['agencies_affected']) . '</div>
                <div class="label">🏢 الوكالات المتأثرة</div>
            </div>
            <div class="summary-card">
                <div class="value value-info">' . number_format($report['summary']['users_affected']) . '</div>
                <div class="label">👤 المستخدمون المتأثرون</div>
            </div>
        </div>';

    // Debtors Table
    if (!empty($report['debtors'])) {
        $html .= '
        <div class="section">
            <h2>📊 تفاصيل المدينين</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>معرف المستخدم</th>
                        <th>الاسم</th>
                        <th>الدين (USD)</th>
                        <th>الدين (ماسات)</th>
                        <th>المستردة</th>
                        <th>غير المستردة</th>
                        <th>نسبة الاسترجاع</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>';

        $i = 0;
        foreach ($report['debtors'] as $debtor) {
            $i++;
            $statusClass = $debtor['status'] == 'fully_recovered' ? 'status-success' : 'status-partial';
            $html .= '
                    <tr>
                        <td>' . $i . '</td>
                        <td>' . $debtor['user_id'] . '</td>
                        <td>' . htmlspecialchars($debtor['user_name']) . '</td>
                        <td style="color: #e74c3c;">$' . number_format($debtor['debt_usd'], 2) . '</td>
                        <td>' . number_format($debtor['debt_coins']) . '</td>
                        <td style="color: #2ecc71;">' . number_format($debtor['recovered_coins']) . '</td>
                        <td style="color: #e74c3c;">' . number_format($debtor['unrecoverable_coins']) . '</td>
                        <td>' . $debtor['recovery_rate'] . '%</td>
                        <td><span class="status-badge ' . $statusClass . '">' . $debtor['status'] . '</span></td>
                    </tr>';
        }

        $html .= '
                </tbody>
            </table>
        </div>';

        // Detailed Traces
        $html .= '
        <div class="section">
            <h2>🔍 تفاصيل التتبع</h2>';

        foreach ($report['debtors'] as $debtor) {
            if (empty($debtor['traces'])) continue;

            $html .= '
            <div style="margin-bottom: 30px;">
                <h3 style="color: #00d9ff; margin-bottom: 15px;">المستخدم: ' . htmlspecialchars($debtor['user_name']) . ' (ID: ' . $debtor['user_id'] . ')</h3>';

            foreach ($debtor['traces'] as $trace) {
                $statusColor = $trace['status'] == 'success' ? '#2ecc71' : '#e74c3c';
                $html .= '
                <div class="trace-item">
                    <div class="type">📍 ' . ucfirst(str_replace('_', ' ', $trace['type'])) . '</div>
                    <div style="margin-top: 8px; color: #aaa;">
                        <p>الهدف: <strong>' . htmlspecialchars($trace['target_name']) . '</strong> (ID: ' . $trace['target_id'] . ')</p>
                        <p>المبلغ المطلوب: <strong>' . number_format($trace['requested_deduction']) . '</strong> ماسة</p>
                        <p>المبلغ المستردة: <strong style="color: #2ecc71;">' . number_format($trace['deducted_amount']) . '</strong> ماسة</p>
                        <p>الحالة: <span style="color: ' . $statusColor . '; font-weight: bold;">' . $trace['status'] . '</span></p>
                    </div>
                </div>';
            }

            $html .= '</div>';
        }

        $html .= '</div>';
    }

    // Action Buttons
    if (!$isLive) {
        $html .= '
        <div style="text-align: center; margin: 40px 0;">
            <p style="color: #f39c12; font-size: 1.2em; margin-bottom: 20px;">
                ⚠️ هذا تقرير معاينة فقط. لم يتم تنفيذ أي تغييرات.
            </p>
            <a href="?fix=1" class="action-btn" onclick="return confirm(\'هل أنت متأكد من تنفيذ الاسترجاع؟ هذا الإجراء لا يمكن التراجع عنه!\');">
                🚀 تنفيذ الاسترجاع الآن
            </a>
            <a href="/direct-recovery/export" class="download-btn">
                📥 تحميل التقرير (CSV)
            </a>
        </div>';
    } else {
        $html .= '
        <div style="text-align: center; margin: 40px 0;">
            <p style="color: #2ecc71; font-size: 1.5em;">
                ✅ تم تنفيذ الاسترجاع بنجاح!
            </p>
            <a href="/direct-recovery/export" class="download-btn">
                📥 تحميل التقرير (CSV)
            </a>
        </div>';
    }

    $html .= '
        <div class="footer">
            <p>Direct Recovery System v1.0</p>
            <p>Generated at ' . $report['timestamp'] . '</p>
        </div>
    </div>
</body>
</html>';

    return response($html)->header('Content-Type', 'text/html; charset=utf-8');
});

/**
 * Export Recovery Report to CSV
 */
Route::get('/direct-recovery/export', function () {
    // Get the latest report data (from session or cache)
    // For now, we'll regenerate it in preview mode
    $isLive = false;
    $zonesCoins = (int) DB::table('settings')->where('key', 'zones_coins')->value('value') ?? 30000;
    $bugDate = '2026-03-01';
    $targetMonth = 3;
    $targetYear = 2026;

    $debtors = DB::select("
        SELECT user_id,
               SUM(sallary) as total_earned,
               SUM(cut_amount) as total_cut,
               SUM(sallary) - SUM(cut_amount) as total_debt
        FROM user_sallaries
        WHERE month = ? AND year = ?
        GROUP BY user_id
        HAVING total_debt < 0
        ORDER BY total_debt ASC
    ", [$targetMonth, $targetYear]);

    $csv = "معرف المستخدم,الاسم,الدين (USD),الدين (ماسات),المستردة,غير المستردة,نسبة الاسترجاع,الحالة\n";

    foreach ($debtors as $debtor) {
        $debtUsd = abs($debtor->total_debt);
        $debtCoins = (int) ($debtUsd * $zonesCoins);
        $remaining = $debtCoins;

        // Simplified recovery calculation (same logic as above)
        $agencyCharges = DB::table('charges')
            ->where('charger_id', $debtor->user_id)
            ->where('charger_type', 'user')
            ->where('user_type', 'agency')
            ->where('created_at', '>=', $bugDate)
            ->sum('amount');

        $recovered = min($remaining, (int) $agencyCharges);
        $remaining -= $recovered;

        $user = DB::table('users')->where('id', $debtor->user_id)->first();
        $recoveryRate = $debtCoins > 0 ? round(($recovered / $debtCoins) * 100, 2) : 0;
        $status = $remaining <= 0 ? 'Fully Recovered' : 'Partially Recovered';

        $csv .= "\"{$debtor->user_id}\",\"{$user->name}\",\"{$debtUsd}\",\"{$debtCoins}\",\"{$recovered}\",\"{$remaining}\",\"{$recoveryRate}%\",\"{$status}\"\n";
    }

    return Response::streamDownload(function () use ($csv) {
        echo $csv;
    }, 'recovery_report_' . now()->format('Y-m-d_H-i-s') . '.csv', [
        'Content-Type' => 'text/csv; charset=utf-8',
        'Content-Disposition' => 'attachment; filename="recovery_report.csv"',
    ]);
});



Route::get('/system-merge-and-recalculate', function (\Illuminate\Http\Request $request) {
    $shouldExecute = $request->query('fix') == '1';
    $targetMonth = 3;
    $targetYear = 2026;

    $report = [
        'mode' => $shouldExecute ? 'LIVE EXECUTION' : 'PREVIEW MODE',
        'timestamp' => now()->toDateTimeString(),
        'total_users_processed' => 0,
        'total_deleted_old_agency_records' => 0,
        'total_merged_current_agency_records' => 0,
        'details' => [],
        'errors' => [],
    ];

    try {
        DB::transaction(function () use ($shouldExecute, $targetMonth, $targetYear, &$report) {

            // 1. جلب كل المستخدمين الذين لديهم سجلات رواتب هذا الشهر
            $usersWithSalaries = DB::table('user_sallaries')
                ->where('month', $targetMonth)
                ->where('year', $targetYear)
                ->distinct()
                ->pluck('user_id');

            foreach ($usersWithSalaries as $userId) {
                // جلب بيانات المستخدم الحالية (وكالته الحالية)
                $user = DB::table('users')->where('id', $userId)->first();
                if (!$user || !$user->agency_id) continue;

                $currentAgencyId = $user->agency_id;

                // أ. حذف أي سجلات راتب للمستخدم تخص وكالات قديمة في نفس الشهر (لأنك تريد الحالية فقط)
                $oldAgencyRecords = DB::table('user_sallaries')
                    ->where('user_id', $userId)
                    ->where('month', $targetMonth)
                    ->where('year', $targetYear)
                    ->where('user_agency_id', '!=', $currentAgencyId);

                $oldRecordsCount = $oldAgencyRecords->count();
                if ($shouldExecute && $oldRecordsCount > 0) {
                    $oldAgencyRecords->delete();
                }

                // ب. التعامل مع تكرار السجلات داخل "الوكالة الحالية"
                $currentAgencyRecords = DB::table('user_sallaries')
                    ->where('user_id', $userId)
                    ->where('month', $targetMonth)
                    ->where('year', $targetYear)
                    ->where('user_agency_id', $currentAgencyId)
                    ->orderBy('id', 'asc')
                    ->get();

                if ($currentAgencyRecords->count() > 0) {
                    $primaryRecord = $currentAgencyRecords->first();
                    $duplicateIds = $currentAgencyRecords->slice(1)->pluck('id')->toArray();
                    $totalCut = $currentAgencyRecords->sum('cut_amount');

                    // ج. حساب الماسات: فقط التي استلمها وهو في الوكالة الحالية
                    $realDiamonds = DB::table('gift_logs')
                        ->where('receiver_id', $userId)
                        ->where('agency_id', $currentAgencyId) // شرط الوكالة الحالية في الهدايا
                        ->whereMonth('created_at', $targetMonth)
                        ->whereYear('created_at', $targetYear)
                        ->where(function($q) {
                            $q->where('source_type', '!=', 'gift')->orWhereNull('source_type');
                        })
                        ->sum('giftPrice');

                    // د. تحديث السجل الرئيسي وتصفير الرواتب لإعادة الحساب
                    if ($shouldExecute) {
                        DB::table('user_sallaries')->where('id', $primaryRecord->id)->update([
                            'cut_amount' => $totalCut,
                            'achieved_diamond' => $realDiamonds,
                            'sallary' => 0,
                            'agency_sallary' => 0,
                            'is_finished' => 0,
                            'target_id' => null
                        ]);

                        if (!empty($duplicateIds)) {
                            DB::table('user_sallaries')->whereIn('id', $duplicateIds)->delete();
                        }

                        DB::table('monthly_diamond_receives')->updateOrInsert(
                            ['user_id' => $userId, 'month' => $targetMonth, 'year' => $targetYear],
                            ['monthly_diamond_received' => $realDiamonds]
                        );
                    }

                    // إضافة البيانات للتقرير
                    $report['details'][] = [
                        'user_id' => $userId,
                        'uuid' => $user->uuid,
                        'current_agency' => $currentAgencyId,
                        'old_records_deleted' => $oldRecordsCount,
                        'duplicates_merged' => count($duplicateIds),
                        'total_cut' => $totalCut,
                        'current_agency_diamonds' => $realDiamonds,
                    ];

                    $report['total_users_processed']++;
                    $report['total_deleted_old_agency_records'] += $oldRecordsCount;
                    $report['total_merged_current_agency_records'] += count($duplicateIds);
                }
            }
        });

        // 2. إعادة تشغيل محرك الرواتب بعد التنظيف
        if ($shouldExecute) {
            $diamondController = app(\App\Http\Controllers\DiamondController::class);
            $diamondController->calculateSalary();
        }

    } catch (\Exception $e) {
        $report['errors'][] = $e->getMessage();
    }

    // ================================================================
    // واجهة التقرير (HTML RTL)
    // ================================================================
    $html = '<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8">
    <style>
        body { font-family: "Segoe UI", Tahoma; background: #0f172a; color: #e2e8f0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: #1e293b; border-radius: 10px; padding: 20px; border: 1px solid #334155; margin-bottom: 20px; }
        h1 { color: #38bdf8; text-align: center; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-box { background: #0f172a; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #334155; }
        .stat-box div:first-child { font-size: 24px; font-weight: bold; color: #34d399; }
        table { width: 100%; border-collapse: collapse; background: #1e293b; }
        th, td { padding: 12px; text-align: center; border-bottom: 1px solid #334155; }
        th { background: #334155; color: #38bdf8; }
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; }
        .mode-preview { background: #f59e0b; color: #000; }
        .mode-live { background: #ef4444; color: #fff; }
        .btn { display: inline-block; padding: 12px 30px; background: #3b82f6; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px; }
    </style></head><body>';

    $html .= '<div class="container">
        <h1>📊 تقرير تنظيف سجلات الوكالة الحالية</h1>
        <div style="text-align:center; margin-bottom:20px;">
            <span class="badge ' . ($shouldExecute ? 'mode-live' : 'mode-preview') . '">' . $report['mode'] . '</span>
        </div>

        <div class="stats">
            <div class="stat-box"><div>' . $report['total_users_processed'] . '</div><div>مستخدم تمت معالجته</div></div>
            <div class="stat-box"><div>' . $report['total_deleted_old_agency_records'] . '</div><div>سجلات وكالات سابقة محذوفة</div></div>
            <div class="stat-box"><div>' . $report['total_merged_current_agency_records'] . '</div><div>سجلات مكررة مدمجة</div></div>
        </div>';

    if (!$shouldExecute) {
        $html .= '<div style="text-align:center;"><a href="?fix=1" class="btn" onclick="return confirm(\'سيتم الآن حذف وحساب الرواتب فعلياً، استمرار؟\')">🚀 تشغيل التنفيذ الفعلي الآن</a></div>';
    }

    $html .= '<div class="card"><h2>📝 تفاصيل المستخدمين</h2><table><thead><tr>
        <th>ID</th><th>UUID</th><th>الوكالة الحالية</th><th>سجلات قديمة (حذف)</th><th>تكرار مدمج</th><th>إجمالي الخصم</th><th>الماس (الوكالة الحالية)</th>
    </tr></thead><tbody>';

    foreach ($report['details'] as $d) {
        $html .= "<tr>
            <td>{$d['user_id']}</td>
            <td>{$d['uuid']}</td>
            <td style='color:#fbbf24'>{$d['current_agency']}</td>
            <td style='color:#f87171'>{$d['old_records_deleted']}</td>
            <td>{$d['duplicates_merged']}</td>
            <td>{$d['total_cut']}</td>
            <td style='color:#38bdf8; font-weight:bold;'>" . number_format($d['current_agency_diamonds']) . "</td>
        </tr>";
    }

    $html .= '</tbody></table></div></div></body></html>';

    return response($html);
});


