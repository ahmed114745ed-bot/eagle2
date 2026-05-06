<?php

// V7 FairLuck Monitor (temp, public, obscured path)
Route::get('monitor/v7/3305d927f49322e0', [\App\Http\Controllers\Api\FairLuckMonitorController::class, 'dashboard']);
Route::get('monitor/v7/3305d927f49322e0/api', [\App\Http\Controllers\Api\FairLuckMonitorController::class, 'apiStats']);

// TEMPORARY: Game duplicate orders check and fix endpoints
// DELETE these routes after the issue is resolved on production
Route::prefix('game-duplicate-check')->group(function () {
    Route::get('/status', [\App\Http\Controllers\Api\V1\GameDuplicateCheckController::class, 'status']);
    Route::post('/migrate', [\App\Http\Controllers\Api\V1\GameDuplicateCheckController::class, 'runMigrations']);
    Route::get('/logs', [\App\Http\Controllers\Api\V1\GameDuplicateCheckController::class, 'logs']);
});

// Coin Game Archive Report
Route::get('/coin-game-archive-report', [\App\Http\Controllers\Api\V1\CoinGameArchiveReportController::class, 'htmlReport'])->name('coin-game-archive-report');
Route::get('/duplicate-cleanup/trigger', function () {
    \Illuminate\Support\Facades\Log::info('=== Cleanup Trigger: Starting CleanupDuplicateOrdersJob directly ===');

    // Run directly (synchronously) instead of dispatching to queue
    $job = new \App\Jobs\CleanupDuplicateOrdersJob();
    $job->handle();

    \Illuminate\Support\Facades\Log::info('CleanupDuplicateOrdersJob completed directly');
    return response()->json([
        'status' => 'queued',
        'message' => 'Cleanup job dispatched to queue. Check logs for progress.',
        'timestamp' => now()->toDateTimeString(),
    ]);
});

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
use App\Models\UserCodeInvitation;
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
use App\Models\Admin;
use App\Models\AdminNotification;
use App\Models\AgencySallary;
use App\Models\Ban;
use App\Models\Bd;
use App\Models\BDSallary;
use App\Models\CoinGameUser;
use App\Models\Coin;
use App\Models\CoinGameUserAll;
use App\Models\CoinGameUserArchive;
use App\Models\CoinGameUserDailyAggregated;
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

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Modules\Form\Http\Controllers\FormTemplateController;
use Modules\Milestones\Entities\Milestone;
use Modules\Milestones\Entities\MilestoneReward;
use Modules\RoomBoom\Entities\TotalRoomGift;
use Modules\RoomBoom\Http\Controllers\web\PercentageBoomController;
use Modules\SuperAdmin\Entities\SuperAdmin;
use Modules\Vip\Entities\VipPrivilege;
use Symfony\Component\Process\Process;
use App\Admin\Controllers\AppearChargerAgencyController;


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


// Protected cache clearing routes (only accessible in local environment with rate limiting)
Route::middleware(['local', 'throttle:5,1'])->group(function () {
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
});

Route::get("download-charge-agency/{agencyId}", function ($agencyId) {
    return Excel::download(new AgencyCharge($agencyId), 'shipping_agency.xlsx');
});
Route::get("download-charge-agency-transactions/{agencyId}", function ($agencyId) {

    return Excel::download(new AgencyChargeTransactions($agencyId), 'shipping_agency.xlsx');
});



Route::get('/count-invite-codes', function () {

    $tz = getTimezone();

    // Start date (GMT+2 → UTC)
    $from = Carbon::parse('Apr 19, 4:27 PM', $tz)->setTimezone('UTC');

    // Current time in UTC
    $to = Carbon::now('UTC');

    $count = UserCodeInvitation::query()->whereBetween('created_at', [$from, $to])->count();

        $accounts = UserCodeInvitation::query()->whereBetween('created_at', [$from, $to])->get();

        $data = [

            'count' => $count,
            'accounts' => $accounts,
            'from' => $from->toDateTimeString(),
            'to' => $to->toDateTimeString(),

        ];

    return response()->json([
        'status' => 'success',
        'data' => $data,
    ]);
});



Route::middleware(['local', 'throttle:5,1'])->get('/clear_clear', function () {
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



// Protected database manipulation routes (only accessible in local environment)
Route::middleware(['local'])->group(function () {
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
});

Route::get('admin/auth', function () {
        return view('checkLogin');
    })->name('admin/auth');
        Route::post('/authenticate', [\App\Admin\Controllers\GameChargeHistoryController::class, 'chickLogin'])->name('authenticate');


Route::get('/config_cache', function () {
    return Artisan::call('config:cache');
});

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
        Route::get('/charge-transfer-settings', [\App\Admin\Controllers\ChargeTransferController::class, 'index'])->name('charge.transfer.settings');
        Route::post('/charge-transfer-settings/save', [\App\Admin\Controllers\ChargeTransferController::class, 'saveSettings'])->name('charge.transfer.settings.save');
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
        Route::get('auth/setting', [\Modules\SuperAdmin\Http\Controllers\AuthController::class, 'getSetting']);
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

Route::middleware(['local'])->get('/clear-admin-error', function () {
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
// Route::get('/calculate-salary' , [\App\Http\Controllers\DiamondController::class, 'calculateSalary']);
// Route::get('/v2/calculate-salary', [\App\Http\Controllers\DiamondController::class, 'calculateSalaryV2']);
// Route::get('monthly-diamond-receive', [\App\Http\Controllers\DiamondController::class, 'copyMonthlyDiamondReceive']);
// Route::get('/sync-bd-agencies', [BdController::class, 'sync']);


Route::get('/charge-agency-export-report', [
    ExportController::class,
    'chargeAgencies'
])->name('charge-agency-export-report');


Route::get('debug-user-level', function () {
    $uuid = request('uuid', '7477526');
    $user = \App\Models\User::withTrashed()
        ->where('uuid', $uuid)
        ->orWhere('id', $uuid)
        ->orWhere('special_id', $uuid)
        ->first();

    if (!$user) {
        $closest = \App\Models\User::where('uuid', 'like', "%$uuid%")->orWhere('name', 'like', "%$uuid%")->limit(5)->get(['id', 'uuid', 'name']);
        return response()->json([
            'status' => 'error',
            'message' => "User with UUID/ID $uuid not found in users table.",
            'search_db_info' => [
                'total_users' => \App\Models\User::withTrashed()->count(),
                'database' => config('database.connections.mysql.database'),
            ],
            'closest_matches_found' => $closest
        ]);
    }

    $currentLevel = $user->senderLevel;
    $expLevel = $user->total_diamond_send;

    $multiplier = config('exp_percentages.exp_sender_percentage', 0.2);
    $currentExp = $expLevel * $multiplier;

    $secondLevel = \Modules\Vip\Entities\Vip::where('type', 2)
        ->where('level', '>', $user->sender_level)
        ->orderBy('level')
        ->first();

    $remaining = max(0, ($secondLevel?->exp ?? 0) - $currentExp);
    $progressCurrent = max(0, $currentExp - ($currentLevel?->exp ?? 0));
    $progressNext = max(1, ($secondLevel?->exp ?? 0) - ($currentLevel?->exp ?? 0));

    return response()->json([
        'status' => 'success',
        'user_data' => [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'special_id' => $user->special_id,
            'name' => $user->name,
            'sender_level_column' => $user->sender_level,
            'total_diamond_send' => $user->total_diamond_send,
            'deleted_at' => $user->deleted_at,
        ],
        'current_level_model' => $currentLevel ? [
            'level' => $currentLevel->level,
            'exp' => $currentLevel->exp,
        ] : 'NULL (The user is likely at level 0 or level not in vips table)',
        'next_level_model' => $secondLevel ? [
            'level' => $secondLevel->level,
            'exp' => $secondLevel->exp,
        ] : 'NULL (Max level or level data missing)',
        'diagnostic_calculations' => [
            'multiplier_used' => $multiplier,
            'earned_xp' => $currentExp,
            'remaining_to_next_level' => $remaining,
            'progress_current' => $progressCurrent,
            'progress_next' => $progressNext,
            'ratio' => $progressNext > 0 ? ($progressCurrent / $progressNext) : 0,
        ],
        'vips_type_2_reference' => \Modules\Vip\Entities\Vip::where('type', 2)->orderBy('level')->get(['level', 'exp']),
        'user' => $user
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
});
Route::get('stuck-users', function () {
    $multiplier = (float) request('multiplier', config('exp_percentages.exp_sender_percentage', 0.2));
    $vips = \Modules\Vip\Entities\Vip::where('type', 2)->orderBy('exp', 'asc')->get();

    $users = \App\Models\User::where('total_diamond_send', '>', 0)->get();

    $stuckUsers = [];

    foreach ($users as $user) {
        $actualExp = $user->total_diamond_send * $multiplier;

        $rightfulLevel = 0;
        foreach ($vips as $vip) {
            if ($actualExp >= $vip->exp) {
                $rightfulLevel = $vip->level;
            } else {
                break;
            }
        }

        if ($rightfulLevel > $user->sender_level) {
            $stuckUsers[] = [
                'user_id' => $user->id,
                'uuid' => $user->uuid,
                'name' => $user->name,
                'total_diamonds' => $user->total_diamond_send,
                'effective_exp_points' => (int) $actualExp,
                'current_level_in_db' => $user->sender_level,
                'should_be_level' => $rightfulLevel,
                'gap' => $rightfulLevel - $user->sender_level
            ];
        }
    }

    return response()->json([
        'status' => 'success',
        'count' => count($stuckUsers),
        'analysis' => [
            'reason_of_stucking' => 'Levels update only on events (Send Gift). Manual point additions or missing triggers leave the sender_level column outdated.',
            'multiplier_source' => [
                'request_param' => request('multiplier') ? 'provided' : 'not provided',
                'config_helper' => config('exp_percentages.exp_sender_percentage'),
                'db_raw' => \DB::table('configs')->where('name', 'exp_sender_percentage')->value('value'),
                'final_multiplier_used' => $multiplier
            ],
            'tip' => 'To test with a different multiplier, use ?multiplier=0.2 in the URL'
        ],
        'stuck_users' => $stuckUsers,
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
});
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


// Gift Logs: fix total diff (dry-run preview) — queries DB directly for accuracy
Route::get('/gift-logs-fix-total-diff/preview', function () {
    $users      = [];
    $totalDiff  = 0;
    $totalUsers = DB::table('users')->where('total_diamond_send', '>', 0)->count();

    DB::table('users')
        ->select('id', 'total_diamond_send')
        ->where('total_diamond_send', '>', 0)
        ->orderBy('id')
        ->chunk(500, function ($chunk) use (&$users, &$totalDiff) {
            foreach ($chunk as $user) {
                $userTotal = (float) ($user->total_diamond_send ?? 0);

                // Sum of real gift logs (excluding correction records)
                $giftLogsTotal = DB::table('gift_logs')
                    ->where('sender_id', $user->id)
                    ->whereNotNull('receiver_id')
                    ->where('receiver_id', '!=', 0)
                    ->where('giftName', '!=', 'diff_correction')
                    ->selectRaw('COALESCE(SUM(CAST(total AS DECIMAL(20,2)) * CAST(giftNum AS DECIMAL(20,2))), 0) as total')
                    ->value('total');

                // Sum of existing correction records
                $correctionTotal = DB::table('gift_logs')
                    ->where('sender_id', $user->id)
                    ->where('giftName', 'diff_correction')
                    ->selectRaw('COALESCE(SUM(CAST(total AS DECIMAL(20,2)) * CAST(giftNum AS DECIMAL(20,2))), 0) as total')
                    ->value('total');

                $giftLogsTotal   = (float) ($giftLogsTotal ?? 0);
                $correctionTotal = (float) ($correctionTotal ?? 0);
                $totalWithCorrection = $giftLogsTotal + $correctionTotal;

                $diff = $userTotal - $totalWithCorrection;

                if (abs($diff) < 1) {
                    continue;
                }

                $users[] = [
                    'user_id'            => (int) $user->id,
                    'total_diamond_send' => $userTotal,
                    'gift_logs_sum'      => $totalWithCorrection,
                    'diff'               => $diff,
                ];
                $totalDiff += $diff;
            }
        });

    $html = view('gift-logs-fix-report', [
        'users'       => $users,
        'total_users' => $totalUsers,
        'total_diff'  => $totalDiff,
        'output'      => '',
    ])->render();

    return response($html)->header('Content-Type', 'text/html; charset=utf-8');
});

// Gift Logs: fix total diff (actual run) — prevents duplicate correction records
Route::get('/gift-logs-fix-total-diff/run', function (\Illuminate\Http\Request $request) {
    $chunk = (int) $request->query('chunk', 500);
    Artisan::call('gift-logs:fix-total-diff', ['--chunk' => $chunk]);
    return response()->json([
        'status'  => 'success',
        'message' => '✅ gift-logs:fix-total-diff executed successfully.',
        'output'  => Artisan::output(),
    ]);
});







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



Route::get('remove-new-country', function () {
    User::where('country_id', 488)->update(['country_id' => null]);

    Country::where('id', 488)->delete();

    return 'done';
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

// Route::get('/backfill-roomcup-rewards', function () {
//     $startDate = '2026-04-04';
//     $endDate = '2026-04-04'; // أمبارح
//     $tz = getTimezone();
//     $results = [];
//     $totalProcessed = 0;
//     $totalRewarded = 0;

//     try {
//         $current = \Carbon\Carbon::parse($startDate, $tz);
//         $end = \Carbon\Carbon::parse($endDate, $tz);

//         while ($current->lte($end)) {
//             $dayStart = $current->copy()->startOfDay()->setTimezone('UTC');
//             $dayEnd = $current->copy()->endOfDay()->setTimezone('UTC');

//             $dayResults = [
//                 'date' => $current->format('Y-m-d'),
//                 'gifts_processed' => 0,
//                 'rewards_given' => 0,
//                 'rooms' => [],
//             ];

//             // جيب الـ gifts لليوم ده
//             $gifts = \Modules\RoomBoom\Entities\TotalRoomGift::whereBetween('created_at', [$dayStart, $dayEnd])
//                 ->orderBy('id')
//                 ->get();

//             foreach ($gifts as $gift) {
//                 $dayResults['gifts_processed']++;
//                 $totalProcessed++;

//                 $room = \App\Models\Room::find($gift->room_id);
//                 if (!$room) {
//                     continue;
//                 }

//                 $adminsCount = $room->admins_v2()->count();
//                 $visitorsCount = $gift->number_of_visitors ?? 0;

//                 // شيك على الـ target
//                 $target = \Modules\RoomCup\Entities\RoomCupTarget::where('total', '<=', $gift->current_total)
//                     ->where('number_of_visitors', '<=', $visitorsCount)
//                     ->orderByDesc('total')
//                     ->first();

//                 if (!$target) {
//                     continue;
//                 }

//                 // عدل الأدمنز (نفس الـ Command)
//                 $room->additional_admin = 0;
//                 $room->save();

//                 $currentTotal = $room->total_admins;
//                 $targetTotal = (int) $target->number_of_admins;
//                 $difference = $targetTotal - $currentTotal;

//                 if ($difference !== 0) {
//                     if ($difference > 0) {
//                         $room->additional_admin += $difference;
//                     } else {
//                         $difference = abs($difference);
//                         $room->additional_admin = max(0, $room->additional_admin - $difference);
//                     }
//                     $room->save();

//                     // Normalize room admins (نفس الـ Command)
//                     $roomAdmin = $room->room_admin;
//                     $roomMax = $room->total_admins;
//                     $configMaxRoom = \App\Helpers\Common::getConfig('max_room_admin') ?? 4;

//                     $adm_arr = ($roomAdmin == '') ? [] : explode(",", trim($roomAdmin));
//                     $adm_arr = array_filter(array_unique($adm_arr));

//                     $allowedMax = ($roomMax >= $configMaxRoom) ? $roomMax : $configMaxRoom;

//                     if (count($adm_arr) > $allowedMax) {
//                         $adm_arr = array_slice($adm_arr, 0, $allowedMax);
//                     }
//                     $str = implode(",", $adm_arr);
//                     $room->update(['room_admin' => $str]);
//                 }

//                 // حضر المكافآت
//                 \Illuminate\Support\Facades\DB::transaction(function () use ($room, $gift, $target, $adminsCount, $dayStart, $dayEnd, &$dayResults, &$totalRewarded) {
//                     $rewards = [];
//                     $targetId = $target->id;

//                     // Owner reward
//                     if ($target->owner_profit > 0) {
//                         $rewards[] = [
//                             'room_id' => $room->id,
//                             'total_room_gift_id' => $gift->id,
//                             'target_id' => $targetId,
//                             'user_id' => $room->uid,
//                             'type' => 'owner',
//                             'amount' => $target->owner_profit,
//                             'created_at' => $dayStart,
//                             'updated_at' => now(),
//                         ];
//                     }

//                     // Admin rewards
//                     if ($adminsCount > 0 && $target->admin_profit > 0) {
//                         $share = $target->admin_profit / $adminsCount;
//                         foreach ($room->admins_v2() as $admin) {
//                             $rewards[] = [
//                                 'room_id' => $room->id,
//                                 'total_room_gift_id' => $gift->id,
//                                 'target_id' => $targetId,
//                                 'user_id' => $admin->id,
//                                 'type' => 'admin',
//                                 'amount' => $share,
//                                 'created_at' => $dayStart,
//                                 'updated_at' => now(),
//                             ];
//                         }
//                     }

//                     foreach ($rewards as $reward) {
//                         // شيك لو الـ reward موجود قبل كده


//                         $nowDate = Carbon::now();
//                         $exists = \Modules\RoomCup\Entities\RoomCupReward::where('room_id', $reward['room_id'])
//                             ->where('user_id', $reward['user_id'])
//                             ->where('type', $reward['type'])
//                             ->whereDate('created_at', $nowDate->toDateString())
//                             ->exists();

//                         if ($exists) {
//                             continue; // skip duplicates
//                         }

//                         // احفظ الـ reward
//                         \Modules\RoomCup\Entities\RoomCupReward::create($reward);
//                         $dayResults['rewards_given']++;
//                         $totalRewarded++;

//                         // زود الفلوس لليوزر
//                         $amountBefore = \App\Helpers\Common::getCurrentBalance($reward['user_id']);

//                         \App\Helpers\UserCoinLogHelper::logByType(
//                             $reward['user_id'],
//                             $reward['amount'],
//                             $amountBefore,
//                             \App\Enums\UserCoinLogType::ROOM_CUP,
//                         );

//                         \App\Models\User::whereKey($reward['user_id'])->increment('di', $reward['amount']);
//                         \Modules\RoomCup\Helpers\RoomCupHelper::updateRoomCupWallet($reward['amount']);

//                         // ابعت notification
//                         $user = \App\Models\User::find($reward['user_id']);
//                         if ($user) {
//                             \App\Facades\CustomNotification::roomcupReward($user, $reward['amount'], $reward['type']);
//                         }
//                     }

//                     if (!empty($rewards)) {
//                         $room->update(['session' => null]);
//                         $dayResults['rooms'][] = $room->id;
//                     }
//                 });
//             }

//             $results[] = $dayResults;
//             $current->addDay();
//         }

//         return response()->json([
//             'success' => true,
//             'message' => 'Backfill completed successfully!',
//             'summary' => [
//                 'total_gifts_processed' => $totalProcessed,
//                 'total_rewards_given' => $totalRewarded,
//                 'days_processed' => count($results),
//             ],
//             'details' => $results,
//         ]);
//     } catch (\Exception $e) {
//         \Illuminate\Support\Facades\Log::error('Backfill RoomCup Error: ' . $e->getMessage(), [
//             'trace' => $e->getTraceAsString()
//         ]);

//         return response()->json([
//             'success' => false,
//             'message' => 'Error during backfill',
//             'error' => $e->getMessage(),
//         ], 500);
//     }
// });


Route::get('/backfill-roomcup-rewards', function () {
    $startDate = '2026-04-05';
    $endDate = '2026-04-05';
    $tz = getTimezone();
    $results = [];
    $totalProcessed = 0;
    $totalRewarded = 0;

    try {
        $current = \Carbon\Carbon::parse($startDate, $tz);
        $end = \Carbon\Carbon::parse($endDate, $tz);

        while ($current->lte($end)) {
            $dayStart = $current->copy()->startOfDay()->setTimezone('UTC');
            $dayEnd = $current->copy()->endOfDay()->setTimezone('UTC');

            $dayResults = [
                'date' => $current->format('Y-m-d'),
                'gifts_processed' => 0,
                'rewards_given' => 0,
                'rooms' => [],
            ];

            \Modules\RoomBoom\Entities\TotalRoomGift::whereBetween('created_at', [$dayStart, $dayEnd])
                ->orderBy('id')
                ->chunk(50, function ($gifts) use (&$dayResults, &$totalProcessed, &$totalRewarded, $dayStart, $dayEnd) {
                    foreach ($gifts as $gift) {
                        $dayResults['gifts_processed']++;
                        $totalProcessed++;

                        $room = \App\Models\Room::find($gift->room_id);
                        if (!$room) {
                            continue;
                        }

                        $adminsCount = $room->admins_v2()->count();
                        $visitorsCount = $gift->number_of_visitors ?? 0;

                        $target = \Modules\RoomCup\Entities\RoomCupTarget::where('total', '<=', $gift->current_total)
                            ->where('number_of_visitors', '<=', $visitorsCount)
                            ->orderByDesc('total')
                            ->first();

                        if (!$target) {
                            continue;
                        }

                        // عدل الأدمنز
                        $room->additional_admin = 0;
                        $room->save();

                        $currentTotal = $room->total_admins;
                        $targetTotal = (int) $target->number_of_admins;
                        $difference = $targetTotal - $currentTotal;

                        if ($difference !== 0) {
                            if ($difference > 0) {
                                $room->additional_admin += $difference;
                            } else {
                                $difference = abs($difference);
                                $room->additional_admin = max(0, $room->additional_admin - $difference);
                            }
                            $room->save();

                            $roomAdmin = $room->room_admin;
                            $roomMax = $room->total_admins;
                            $configMaxRoom = \App\Helpers\Common::getConfig('max_room_admin') ?? 4;

                            $adm_arr = ($roomAdmin == '') ? [] : explode(",", trim($roomAdmin));
                            $adm_arr = array_filter(array_unique($adm_arr));

                            $allowedMax = ($roomMax >= $configMaxRoom) ? $roomMax : $configMaxRoom;

                            if (count($adm_arr) > $allowedMax) {
                                $adm_arr = array_slice($adm_arr, 0, $allowedMax);
                            }
                            $str = implode(",", $adm_arr);
                            $room->update(['room_admin' => $str]);
                        }

                        // حضر المكافآت
                        \Illuminate\Support\Facades\DB::transaction(function () use ($room, $gift, $target, $adminsCount, $dayStart, $dayEnd, &$dayResults, &$totalRewarded) {
                            $rewards = [];
                            $targetId = $target->id;

                            if ($target->owner_profit > 0) {
                                $rewards[] = [
                                    'room_id' => $room->id,
                                    'total_room_gift_id' => $gift->id,
                                    'target_id' => $targetId,
                                    'user_id' => $room->uid,
                                    'type' => 'owner',
                                    'amount' => $target->owner_profit,
                                    'created_at' => $dayStart,
                                    'updated_at' => now(),
                                ];
                            }

                            if ($adminsCount > 0 && $target->admin_profit > 0) {
                                $share = $target->admin_profit / $adminsCount;
                                foreach ($room->admins_v2() as $admin) {
                                    $rewards[] = [
                                        'room_id' => $room->id,
                                        'total_room_gift_id' => $gift->id,
                                        'target_id' => $targetId,
                                        'user_id' => $admin->id,
                                        'type' => 'admin',
                                        'amount' => $share,
                                        'created_at' => $dayStart,
                                        'updated_at' => now(),
                                    ];
                                }
                            }

                            foreach ($rewards as $reward) {
                                // شيك لو الـ reward موجود قبل كده
                                $nowDate = \Carbon\Carbon::now();
                                $exists = \Modules\RoomCup\Entities\RoomCupReward::where('room_id', $reward['room_id'])
                                    ->where('user_id', $reward['user_id'])
                                    ->where('type', $reward['type'])
                                    ->whereDate('created_at', $nowDate->toDateString())
                                    ->exists();

                                if ($exists) {
                                    continue;
                                }

                                \Modules\RoomCup\Entities\RoomCupReward::create($reward);
                                $dayResults['rewards_given']++;
                                $totalRewarded++;

                                $amountBefore = \App\Helpers\Common::getCurrentBalance($reward['user_id']);

                                \App\Helpers\UserCoinLogHelper::logByType(
                                    $reward['user_id'],
                                    $reward['amount'],
                                    $amountBefore,
                                    \App\Enums\UserCoinLogType::ROOM_CUP,
                                );

                                \App\Models\User::whereKey($reward['user_id'])->increment('di', $reward['amount']);
                                \Modules\RoomCup\Helpers\RoomCupHelper::updateRoomCupWallet($reward['amount']);

                                $user = \App\Models\User::find($reward['user_id']);
                                if ($user) {
                                    \App\Facades\CustomNotification::roomcupReward($user, $reward['amount'], $reward['type']);
                                }
                            }

                            if (!empty($rewards)) {
                                $room->update(['session' => null]);
                                $dayResults['rooms'][] = $room->id;
                            }
                        });
                    }
                });

            $results[] = $dayResults;
            $current->addDay();
        }

        return response()->json([
            'success' => true,
            'message' => 'Backfill completed successfully!',
            'summary' => [
                'total_gifts_processed' => $totalProcessed,
                'total_rewards_given' => $totalRewarded,
                'days_processed' => count($results),
            ],
            'details' => $results,
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Backfill RoomCup Error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error during backfill',
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/backfill-roomcup-weekly-rewards', function () {
    $tz = getTimezone();
    $totalProcessed = 0;
    $totalRewarded = 0;
    $roomDetails = [];

    try {
        $weekStart = \Carbon\Carbon::now($tz)->subWeek()->startOfWeek()->setTimezone('UTC');
        $weekEnd = \Carbon\Carbon::now($tz)->subWeek()->endOfWeek()->setTimezone('UTC');

        $minTarget = \Modules\RoomCup\Entities\RoomCupTarget::min('total');

        $aggregatedGifts = \App\Models\GiftLog::whereBetween('created_at', [$weekStart, $weekEnd])
            ->select(
                'room_id',
                \Illuminate\Support\Facades\DB::raw('SUM(giftPrice) as current_total'),
            )
            ->whereNotNull('room_id')
            ->groupBy('room_id')
            ->having('current_total', '>=', $minTarget)
            ->orderBy('room_id')
            ->get();

        // Get number_of_visitors from TotalRoomGift per room
        $visitorsMap = \Modules\RoomBoom\Entities\TotalRoomGift::whereBetween('created_at', [$weekStart, $weekEnd])
            ->select(
                'room_id',
                \Illuminate\Support\Facades\DB::raw('SUM(number_of_visitors) as number_of_visitors')
            )
            ->groupBy('room_id')
            ->pluck('number_of_visitors', 'room_id');

        foreach ($aggregatedGifts as $gift) {
            $totalProcessed++;

            $room = \App\Models\Room::find($gift->room_id);
            if (!$room) {
                continue;
            }

            $adminsCount = $room->admins_v2()->count();
            $visitorsCount = $visitorsMap[$gift->room_id] ?? 0;

            $target = \Modules\RoomCup\Entities\RoomCupTarget::where('total', '<=', $gift->current_total)
                // ->where('number_of_visitors', '<=', $visitorsCount)
                ->orderByDesc('total')
                ->first();

            if (!$target) {
                continue;
            }

            // Create or get TotalRoomGift record for foreign key constraint
            $totalRoomGift = \Modules\RoomBoom\Entities\TotalRoomGift::firstOrCreate(
                [
                    'room_id' => $gift->room_id,
                    'created_at' => $weekStart,
                ],
                [
                    'current_total' => $gift->current_total,
                    'number_of_visitors' => $visitorsCount,
                    'updated_at' => now(),
                ]
            );

            // Assign the TotalRoomGift id to use in rewards
            $gift->id = $totalRoomGift->id;

            $room->additional_admin = 0;
            $room->save();

            $currentTotal = $room->total_admins;
            $targetTotal = (int) $target->number_of_admins;
            $difference = $targetTotal - $currentTotal;

            if ($difference !== 0) {
                if ($difference > 0) {
                    $room->additional_admin += $difference;
                } else {
                    $difference = abs($difference);
                    $room->additional_admin = max(0, $room->additional_admin - $difference);
                }
                $room->save();

                $roomAdmin = $room->room_admin;
                $roomMax = $room->total_admins;
                $configMaxRoom = \App\Helpers\Common::getConfig('max_room_admin') ?? 4;

                $adm_arr = ($roomAdmin == '') ? [] : explode(",", trim($roomAdmin));
                $adm_arr = array_filter(array_unique($adm_arr));

                $allowedMax = ($roomMax >= $configMaxRoom) ? $roomMax : $configMaxRoom;

                if (count($adm_arr) > $allowedMax) {
                    $adm_arr = array_slice($adm_arr, 0, $allowedMax);
                }
                $str = implode(",", $adm_arr);
                $room->update(['room_admin' => $str]);
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($room, $gift, $target, $adminsCount, $weekStart, $weekEnd, &$roomDetails, &$totalRewarded) {
                $rewards = [];
                $targetId = $target->id;

                if ($target->owner_profit > 0) {
                    $rewards[] = [
                        'room_id' => $room->id,
                        'total_room_gift_id' => $gift->id,
                        'target_id' => $targetId,
                        'user_id' => $room->uid,
                        'type' => 'owner',
                        'amount' => $target->owner_profit,
                        'created_at' => $weekStart,
                        'updated_at' => now(),
                    ];
                }

                if ($adminsCount > 0 && $target->admin_profit > 0) {
                    $share = $target->admin_profit / $adminsCount;
                    foreach ($room->admins_v2() as $admin) {
                        $rewards[] = [
                            'room_id' => $room->id,
                            'total_room_gift_id' => $gift->id,
                            'target_id' => $targetId,
                            'user_id' => $admin->id,
                            'type' => 'admin',
                            'amount' => $share,
                            'created_at' => $weekStart,
                            'updated_at' => now(),
                        ];
                    }
                }

                foreach ($rewards as $reward) {
                    $exists = \Modules\RoomCup\Entities\RoomCupReward::where('room_id', $reward['room_id'])
                        ->where('user_id', $reward['user_id'])
                        ->where('type', $reward['type'])
                        ->whereBetween('created_at', [$weekStart, $weekEnd])
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    \Modules\RoomCup\Entities\RoomCupReward::create($reward);
                    $totalRewarded++;

                    $amountBefore = \App\Helpers\Common::getCurrentBalance($reward['user_id']);

                    \App\Helpers\UserCoinLogHelper::logByType(
                        $reward['user_id'],
                        $reward['amount'],
                        $amountBefore,
                        \App\Enums\UserCoinLogType::ROOM_CUP,
                    );

                    \App\Models\User::whereKey($reward['user_id'])->increment('di', $reward['amount']);
                    \Modules\RoomCup\Helpers\RoomCupHelper::updateRoomCupWallet($reward['amount']);

                    $user = \App\Models\User::find($reward['user_id']);
                    if ($user) {
                        \App\Facades\CustomNotification::roomcupReward($user, $reward['amount'], $reward['type']);
                    }
                }

                if (!empty($rewards)) {
                    $room->update(['session' => null]);
                    $roomDetails[] = $room->id;
                }
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Weekly backfill completed successfully!',
            'summary' => [
                'week' => $weekStart->toDateString() . ' to ' . $weekEnd->toDateString(),
                'total_rooms_processed' => $totalProcessed,
                'total_rewards_given' => $totalRewarded,
                'rooms_rewarded' => $roomDetails,
            ],
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Backfill Weekly RoomCup Error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error during weekly backfill',
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/update-reward-dates', function () {

    $rewards = \Modules\RoomCup\Entities\RoomCupReward::all();

    $results = [];

    foreach ($rewards as $reward) {
        $oldDate = $reward->created_at->copy();

        if ($oldDate->isSaturday()) {
            $newDate = $oldDate->copy()->subWeek();
        } else {
            $newDate = $oldDate->copy()->previous(Carbon::SATURDAY);
        }

        \Illuminate\Support\Facades\DB::table('room_cup_rewards')
            ->where('id', $reward->id)
            ->update([
                'created_at' => $newDate,
                'updated_at' => $newDate,
            ]);

        $results[] = [
            'id'       => $reward->id,
            'old_date' => $oldDate->toDateTimeString(),
            'new_date' => $newDate->toDateTimeString(),
        ];
    }

    return response()->json([
        'total_updated' => count($results),
        'details'       => $results,
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




Route::middleware('local')->get('/run-lucky-gift-test', function () {
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

Route::middleware('local')->get('/run-lucky-gift-unit-test', function () {
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
            ->lockForUpdate()
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


    $logs = CoinLog::whereNull('paid_usd')
        ->orWhere('paid_usd', 0)
        ->get();


    $updated = 0;
    $skipped = 0;
    $errors = 0;

    foreach ($logs as $log) {

        try {


            $coin = Coin::where('coin', $log->obtained_coins)->first();

            if ($coin) {

                $oldValue = $log->paid_usd;

                $log->paid_usd = $coin->usd;
                $saved = $log->save();

                if ($saved) {
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


    return "Updated: {$updated} | Skipped: {$skipped} | Errors: {$errors}";
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
    $hostPercentage  = getGiftPercentage('host_lucky_gift')  / 10;
    dd($negativeLimit, $appFeeRate, $receiverFeeRate,$hostPercentage);
});

// Step 1: Diagnostic - show affected lucky gift logs (100% instead of 10%)
Route::get('/fix-gift-logs/check', function () {
    $affected = DB::select("
        SELECT
            gl.id,
            gl.giftId,
            gl.giftNum,
            gl.giftPrice as logged_price,
            gl.receiver_obtain,
            g.price as actual_gift_price,
            g.type as gift_type,
            (gl.giftNum * g.price) as expected_full_price,
            ROUND(gl.giftPrice / (gl.giftNum * g.price), 2) as current_ratio,
            ROUND(gl.giftNum * g.price * 0.1, 2) as correct_10_percent,
            gl.created_at
        FROM gift_logs gl
        JOIN gifts g ON gl.giftId = g.id
        WHERE g.type = 6
        AND gl.giftNum > 0
        AND g.price > 0
        AND gl.giftPrice = gl.giftNum * g.price
        ORDER BY gl.id DESC
        LIMIT 50
    ");

    $totalAffected = DB::selectOne("
        SELECT COUNT(*) as total
        FROM gift_logs gl
        JOIN gifts g ON gl.giftId = g.id
        WHERE g.type = 6
        AND gl.giftNum > 0
        AND g.price > 0
        AND gl.giftPrice = gl.giftNum * g.price
    ");

    return response()->json([
        'total_affected_records' => $totalAffected->total,
        'sample_records' => $affected,
        'message' => 'These records have giftPrice at 100% instead of 10%. Go to /fix-gift-logs/run to fix them.',
    ]);
});

// Step 2: Fix - update affected records to 10%
Route::get('/fix-gift-logs/run', function () {
    $affected = DB::selectOne("
        SELECT COUNT(*) as total
        FROM gift_logs gl
        JOIN gifts g ON gl.giftId = g.id
        WHERE g.gift_category_id = 7
        AND gl.giftNum > 0
        AND g.price > 0
        AND gl.giftPrice = gl.giftNum * g.price
    ");

    if ($affected->total == 0) {
        return response()->json(['message' => 'No records to fix.']);
    }

    $updated = DB::update("
        UPDATE gift_logs gl
        JOIN gifts g ON gl.giftId = g.id
        SET
            gl.roomowner_obtain = FLOOR(gl.giftPrice * 0.1 * 0.03),
            gl.app_profit_coins = gl.giftPrice * 0.1,
            gl.receiver_obtain = gl.giftPrice * 0.1,
            gl.giftPrice = gl.giftPrice * 0.1
        WHERE g.gift_category_id = 7
        AND gl.giftNum > 0
        AND g.price > 0
        AND gl.giftPrice = gl.giftNum * g.price
    ");

    return response()->json([
        'message' => "Fixed {$updated} records. giftPrice, receiver_obtain, app_profit_coins updated to 10%.",
        'records_updated' => $updated,
    ]);
});


Route::get('/fix-charges-usd', function () {
    $dryRun = request()->get('fix') != '1';

    // Get the 4 rates from settings
    $rates = [
        'user_coins' => (float) (\Cache::rememberForever('user_coins', function () {
            return \App\Models\Setting::where('key', 'user_coins')->value('value') ?? 1;
        })),
        'shipping_coins' => (float) (\Cache::rememberForever('shipping_coins', function () {
            return \App\Models\Setting::where('key', 'shipping_coins')->value('value') ?? 1;
        })),
        'zones_coins' => (float) (\Cache::rememberForever('zones_coins', function () {
            return \App\Models\Setting::where('key', 'zones_coins')->value('value') ?? 1;
        })),
        'super_admin_coins' => (float) (\Cache::rememberForever('super_admin_coins', function () {
            return \App\Models\Setting::where('key', 'super_admin_coins')->value('value') ?? 1;
        })),
    ];

    // Map user_type to the correct rate key
    $typeToRate = [
        'agency' => 'shipping_coins',
        'user' => 'user_coins',
        'host' => 'user_coins',
        'app' => 'user_coins',
        'Host agent' => 'user_coins',
        'freight forwarder' => 'shipping_coins',
        'freight forwarder and Host agent' => 'shipping_coins',
        'Administrative' => 'user_coins',
        \App\Enums\Charges\UserTypeEnum::SUPER_ADMIN => 'super_admin_coins',
        \App\Enums\Charges\UserTypeEnum::SUB_ADMIN => 'super_admin_coins',
        \App\Enums\Charges\UserTypeEnum::AREA_MANAGER => 'zones_coins',
        \App\Enums\Charges\UserTypeEnum::SUB_AREA_MANAGER => 'zones_coins',
    ];

    $stats = [
        'negative_amount_positive_usd' => 0,
        'usd_is_zero' => 0,
        'usd_equals_amount' => 0,
        'total_fixed' => 0,
        'skipped_unknown_type' => 0,
    ];

    $samples = [];

    // Case 1: amount is negative but usd is positive (or zero)
    \App\Models\Charge::where('amount', '<', 0)
        ->where('usd', '>=', 0)
        ->chunkById(500, function ($charges) use ($dryRun, $rates, $typeToRate, &$stats, &$samples) {
            foreach ($charges as $charge) {
                $stats['negative_amount_positive_usd']++;

                $rateKey = $typeToRate[$charge->user_type] ?? 'user_coins';
                $rate = $rates[$rateKey] ?: 1;
                $correctUsd = $charge->amount / $rate; // will be negative since amount is negative

                if (count($samples) < 20) {
                    $samples[] = [
                        'id' => $charge->id,
                        'case' => 'negative_amount_positive_usd',
                        'amount' => $charge->amount,
                        'old_usd' => $charge->usd,
                        'new_usd' => round($correctUsd, 4),
                        'user_type' => $charge->user_type,
                        'rate_used' => $rateKey . '=' . $rate,
                    ];
                }

                if (!$dryRun) {
                    $charge->usd = $correctUsd;
                    $charge->saveQuietly();
                    $stats['total_fixed']++;
                }
            }
        });

    // Case 2: usd is 0 or null but amount is not 0
    \App\Models\Charge::where('amount', '!=', 0)
        ->where(function ($q) {
            $q->whereNull('usd')->orWhere('usd', 0);
        })
        ->chunkById(500, function ($charges) use ($dryRun, $rates, $typeToRate, &$stats, &$samples) {
            foreach ($charges as $charge) {
                $stats['usd_is_zero']++;

                $rateKey = $typeToRate[$charge->user_type] ?? 'user_coins';
                $rate = $rates[$rateKey] ?: 1;
                $correctUsd = $charge->amount / $rate;

                if (count($samples) < 40) {
                    $samples[] = [
                        'id' => $charge->id,
                        'case' => 'usd_is_zero',
                        'amount' => $charge->amount,
                        'old_usd' => $charge->usd,
                        'new_usd' => round($correctUsd, 4),
                        'user_type' => $charge->user_type,
                        'rate_used' => $rateKey . '=' . $rate,
                    ];
                }

                if (!$dryRun) {
                    $charge->usd = $correctUsd;
                    $charge->saveQuietly();
                    $stats['total_fixed']++;
                }
            }
        });

    // Case 3: usd equals amount (not converted) — only when rate != 1
    \App\Models\Charge::where('amount', '!=', 0)
        ->whereColumn('usd', 'amount')
        ->chunkById(500, function ($charges) use ($dryRun, $rates, $typeToRate, &$stats, &$samples) {
            foreach ($charges as $charge) {
                $rateKey = $typeToRate[$charge->user_type] ?? 'user_coins';
                $rate = $rates[$rateKey] ?: 1;

                // Skip if rate is 1 (usd = amount is correct)
                if ($rate == 1) continue;

                $stats['usd_equals_amount']++;
                $correctUsd = $charge->amount / $rate;

                if (count($samples) < 60) {
                    $samples[] = [
                        'id' => $charge->id,
                        'case' => 'usd_equals_amount',
                        'amount' => $charge->amount,
                        'old_usd' => $charge->usd,
                        'new_usd' => round($correctUsd, 4),
                        'user_type' => $charge->user_type,
                        'rate_used' => $rateKey . '=' . $rate,
                    ];
                }

                if (!$dryRun) {
                    $charge->usd = $correctUsd;
                    $charge->saveQuietly();
                    $stats['total_fixed']++;
                }
            }
        });

    return response()->json([
        'status' => $dryRun ? 'preview' : 'executed',
        'stats' => $stats,
        'samples' => $samples,
    ]);
});

// ================================================================
// Direct Recovery V2 - Dispatch Job
// ================================================================
Route::get('/direct-recovery-v2', function () {
    \App\Jobs\DirectRecoveryJob::dispatch();
    return response()->json([
        'status' => 'dispatched',
        'message' => 'DirectRecoveryJob has been queued. Check public/ for the report when done.',
    ]);
});



Route::get('v2/system-merge-only-duplicates', function (\Illuminate\Http\Request $request) {
    $shouldExecute = $request->query('fix') == '1';
    $targetMonth = 3;
    $targetYear = 2026;
    $thresholdDate = "2026-03-19 00:00:00";

    $startOfMonth = \Carbon\Carbon::create($targetYear, $targetMonth, 1)->startOfMonth();
    $endOfMonth = \Carbon\Carbon::create($targetYear, $targetMonth, 1)->endOfMonth();

       DB::table('gift_logs')
            ->where('source_type', 'gift')
            ->where('created_at', '>=', $thresholdDate)
            ->delete();

    $report = [
        'mode' => $shouldExecute ? 'LIVE EXECUTION' : 'PREVIEW MODE',
        'gifts_to_delete' => 0,
        'users_count' => 0,
        'users_details' => [],
    ];

    // عدد الهدايا اللي هتتحذف
    $report['gifts_to_delete'] = DB::table('gift_logs')
        ->where('source_type', 'gift')
        ->where('created_at', '>=', $thresholdDate)
        ->count();

    $users = DB::table('users')->where('agency_id', '!=', null)->where('agency_id', '>', 0)->get();
    $report['users_count'] = $users->count();

    foreach ($users as $user) {
        if (!$user || !$user->agency_id) continue;

        $currentAgencyId = $user->agency_id;

        $joinRequest = DB::table('agency_join_requests')
            ->where('user_id', $user->id)
            ->where('agency_id', $currentAgencyId)
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->first();

        $joinTime = $joinRequest ? $joinRequest->created_at : $startOfMonth->toDateTimeString();
        $effectiveStartTime = max($joinTime, $startOfMonth->toDateTimeString());

        // الماس بدون الهدايا اللي هتتحذف
        $realDiamonds = DB::table('gift_logs')
            ->where('receiver_id', $user->id)
            ->where('agency_id', $currentAgencyId)
            ->whereBetween('created_at', [$effectiveStartTime, $endOfMonth])
            ->sum('giftPrice');

        $totalCutAmount = DB::table('user_sallaries')
            ->where(['user_id' => $user->id, 'month' => $targetMonth, 'year' => $targetYear])
             ->where('user_agency_id', $currentAgencyId)
            ->sum('cut_amount');

        $salaryRecordsCount = DB::table('user_sallaries')
            ->where(['user_id' => $user->id, 'month' => $targetMonth, 'year' => $targetYear])
             ->where('user_agency_id', $currentAgencyId)
            ->count();

        $currentDiamond = DB::table('monthly_diamond_receives')
            ->where(['user_id' => $user->id, 'month' => $targetMonth, 'year' => $targetYear])
            ->value('monthly_diamond_received');

        // نضيف في التقرير لو فيه تغيير
        if ($salaryRecordsCount > 1 || $currentDiamond != $realDiamonds) {
            $report['users_details'][] = [
                'user_id' => $user->id,
                'agency_id' => $currentAgencyId,
                'salary_records' => $salaryRecordsCount,
                'total_cut' => $totalCutAmount,
                'current_diamond' => $currentDiamond,
                'real_diamond' => $realDiamonds,
            ];
        }

        if ($shouldExecute) {
            DB::table('monthly_diamond_receives')->updateOrInsert(
                ['user_id' => $user->id, 'month' => $targetMonth, 'year' => $targetYear],
                ['monthly_diamond_received' => $realDiamonds]
            );

            DB::table('user_sallaries')
                ->where(['user_id' => $user->id, 'month' => $targetMonth, 'year' => $targetYear])
                ->delete();

            DB::table('user_sallaries')->insert([
                'user_id' => $user->id,
                'month' => $targetMonth,
                'year' => $targetYear,
                'user_agency_id' => $currentAgencyId,
                'achieved_diamond' => $realDiamonds,
                'cut_amount' => $totalCutAmount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')->where('id', $user->id)->update(['salary_is_updated' => 1]);
        }
    }

    return response()->json($report);
});



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
                // 1. جلب المستلمين والمبالغ التي حصلوا عليها من المديون
                $gifts = DB::select("
                    SELECT receiver_id, SUM(giftPrice) as total_sent
                    FROM gift_logs
                    WHERE sender_id = ? 
                    AND created_at >= ? 
                    AND created_at < '2026-04-01'
                    GROUP BY receiver_id
                    ORDER BY total_sent DESC
                    LIMIT 100
                ", [$debtor->user_id, $bugDate]);

                foreach ($gifts as $gift) {
                    if ($remaining <= 0) break;

                    $receiverId = $gift->receiver_id;
                    $giftAmount = (int) $gift->total_sent;
                    $deductAmount = min($remaining, $giftAmount);

                    // 2. البحث في جدول الماسات الشهرية للمستلم
                    $monthlyRecord = DB::table('monthly_diamond_receives')
                        ->where('user_id', $receiverId)
                        ->where('month', $targetMonth)
                        ->where('year', $targetYear)
                        ->first();

                    if (!$monthlyRecord) continue;

                    // الرصيد القابل للخصم هو ما استلمه فعلياً هذا الشهر
                    $currentMonthlyDiamonds = (int) $monthlyRecord->monthly_diamond_received;
                    $canDeduct = min($deductAmount, $currentMonthlyDiamonds);

                    $trace = [
                        'type' => 'monthly_gift_recovery',
                        'target_id' => $receiverId,
                        'target_name' => DB::table('users')->where('id', $receiverId)->value('name') ?? 'N/A',
                        'gift_amount' => $giftAmount,
                        'requested_deduction' => $deductAmount,
                        'monthly_diamonds_before' => $currentMonthlyDiamonds,
                        'deducted_amount' => $canDeduct,
                        'status' => $canDeduct >= $deductAmount ? 'success' : 'partial_from_monthly',
                    ];

                    if ($isLive && $canDeduct > 0) {
                        // 3. التحديث الفعلي في جدول الماسات الشهرية وليس جدول المستخدمين
                        DB::table('monthly_diamond_receives')
                            ->where('id', $monthlyRecord->id)
                            ->update([
                                'monthly_diamond_received' => DB::raw("monthly_diamond_received - $canDeduct")
                            ]);
                    }

                    $remaining -= $canDeduct;
                    $debtorDetail['recovered_coins'] += $canDeduct;
                    $debtorDetail['traces'][] = $trace;
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
            // Final Status: Update debtor's salary record with recovered amount
            // ================================================================
            $debtorDetail['unrecoverable_coins'] = $remaining;
            $debtorDetail['recovery_rate'] = $debtCoins > 0 
                ? round(($debtorDetail['recovered_coins'] / $debtCoins) * 100, 2) 
                : 0;
            $debtorDetail['status'] = $remaining <= 0 ? 'fully_recovered' : 'partially_recovered';

            // Convert recovered coins to USD for cut_amount update
            // cut_amount should be in USD, not coins
            $recoveredUsd = $debtorDetail['recovered_coins'] > 0 
                ? round($debtorDetail['recovered_coins'] / $zonesCoins, 2)
                : 0;

            // Update the debtor's salary record to increase cut_amount by recovered USD amount
            // This shows that the recovered amount has been deducted from their debt
            if ($isLive && $recoveredUsd > 0) {
                DB::table('user_sallaries')
                    ->where('user_id', $debtor->user_id)
                    ->where('month', $targetMonth)
                    ->where('year', $targetYear)
                    ->update([
                        'cut_amount' => DB::raw("CAST(cut_amount AS SIGNED) + " . $recoveredUsd),
                    ]);
            }

            $report['summary']['total_recovered_coins'] += $debtorDetail['recovered_coins'];
            $report['summary']['total_unrecoverable_coins'] += $remaining;

            $report['debtors'][] = $debtorDetail;
        }
        app(\App\Http\Controllers\DiamondController::class)->calculateSalary();
        

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



Route::get('/shami2', function () {
   return response()->json([
       'status' => 'success',
       'message' => 'successfully.',
   ]);
});
// ================================================================
// Recovery Routes
// ================================================================
Route::get('/direct-recovery-v2', function () {
    \App\Jobs\DirectRecoveryJob::dispatch();
    return response()->json([
        'status' => 'dispatched',
        'message' => 'DirectRecoveryJob has been queued. Check public/ for the report when done.',
    ]);
});

Route::get('/agency-recovery', function () {
    \App\Jobs\AgencyRecoveryJob::dispatch();
    return response()->json([
        'status' => 'dispatched',
        'message' => 'AgencyRecoveryJob has been queued. Check public/agency_recovery_report.html for the report.',
    ]);
});

Route::get('/master-recovery', function () {
    @unlink(public_path('master_recovery_report.html'));
    @unlink(public_path('master_recovery_deductions.csv'));
    @unlink(public_path('direct_recovery_report.html'));
    @unlink(public_path('agency_recovery_report.html'));

    \App\Jobs\MasterRecoveryJob::dispatch();
    return response()->json([
        'status' => 'dispatched',
        'message' => 'MasterRecoveryJob started. Check /master_recovery_report.html for progress, /master_recovery_deductions.csv for the deductions sheet.',
    ]);
});

Route::get('/test-push-succ', function () {
   dd("test push succ");
    return response()->json([
        'status' => 'dispatched',
        'message' => 'AgencyRecoveryJob has been queued. Check public/agency_recovery_report.html for the report.',
    ]);
});



//Route::get('load-default-queue', function () {
//    for ($i = 0; $i < 50000; $i++) {
//        dispatch((new \App\Jobs\SlowTestJob())->onQueue('default'));
//    }
//})->middleware('local');

Route::get('test-done', function () {
    return 17;
});

Route::get('clean-duplicates', [\App\Admin\Controllers\CustomController::class, 'cleanDuplicates'])->name('clean.duplicates');

Route::get('/update-user-monthly-diamonds/{id}', function ($id) {
    $userId = $id;
    $month = 4; // April
    $year = 2026;

    // Get parameters (same as gift-log query)
    $giftType = request('gift_type', 'receiver');
    $start = request('start_at');
    $end = request('end_at');
    $agencyId = request('agency_id');
    $timezone = Common::timeZone();

    // Build query exactly like gift-log tab
    $giftBaseQuery = GiftLog::query()
        ->when($giftType === 'receiver', fn($q) => $q->where('receiver_id', $userId))
        ->when($start && $end, fn($q) => $q->whereBetween('created_at', [
            Carbon::parse($start, $timezone)->startOfDay()->utc(),
            Carbon::parse($end, $timezone)->endOfDay()->utc(),
        ]));

    // Calculate total diamonds (same as gift-log)
    $totalDiamonds = $giftBaseQuery->sum('giftPrice');

    // Update or create record in monthly_diamond_receives
    \App\Models\MonthlyDiamondReceive::updateOrCreate(
        [
            'user_id' => $userId,
            'month' => $month,
            'year' => $year,
        ],
        [
            'monthly_diamond_received' => $totalDiamonds,
        ]
    );

    return response()->json([
        'status' => 'success',
        'user_id' => $userId,
        'gift_type' => $giftType,
        'start_at' => $start,
        'end_at' => $end,
        'agency_id' => $agencyId,
        'month' => $month,
        'year' => $year,
        'total_diamonds' => $totalDiamonds,
        'message' => "تم تحديث مجموع الماسات الشهرية للمستخدم {$userId} بنجاح"
    ]);
});

Route::get('/set-lucky-version-7', function () {

    $version = 4;

    \App\Models\Setting::updateOrCreate(
        ['key' => 'lucky_gift_version'],
        ['value' => $version]
    );

    Cache::forget('lucky_gift_version');
    Cache::put('lucky_gift_version', $version);

    return response()->json([
        'status' => true,
        'message' => 'Version updated successfully',
        'current_version' => $version,
        'cached_version' => Cache::get('lucky_gift_version')
    ]);
});
