<?php

use App\Admin\Controllers\AgencyController;
use App\Admin\Controllers\AuthController;
use App\Admin\Controllers\ExportController;
use App\Admin\Controllers\MangerSettingController;
use App\Admin\Controllers\UserController;
use App\Admin\Controllers\V2\SalariesController;
use App\Exports\AgencyCharge;
use App\Exports\AgencyChargeTransactions;
use App\Http\Controllers\addTOjesonController;
use App\Http\Controllers\Api\V1\ConfigController;
use App\Http\Controllers\Api\V1\GiftLogController;
use App\Http\Controllers\Api\V2\MallController;
use App\Http\Controllers\NowPaymentsController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WelcomeController;
use App\Models\RoomVisitor;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Modules\RoomBoom\Http\Controllers\web\PercentageBoomController;
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



// Coin Game Archive Report
Route::get('/coin-game-archive-report', [\App\Http\Controllers\Api\V1\CoinGameArchiveReportController::class, 'htmlReport'])->name('coin-game-archive-report');
Route::get('/duplicate-cleanup/trigger', function () {
    \Illuminate\Support\Facades\Log::info('=== Cleanup Trigger: Starting CleanupDuplicateOrdersJob directly ===');

    $job = new \App\Jobs\CleanupDuplicateOrdersJob();
    $job->handle();

    \Illuminate\Support\Facades\Log::info('CleanupDuplicateOrdersJob completed directly');
    return response()->json([
        'status' => 'completed',
        'message' => 'Cleanup job completed. Check logs for details.',
        'timestamp' => now()->toDateTimeString(),
    ]);
});






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

Route::prefix('payment')->group(function () {
    Route::get('payment-success', [\App\Http\Controllers\Web\PaymentController::class, 'success']);
    Route::get('payment-fail', [\App\Http\Controllers\Web\PaymentController::class, 'fail']);
});

Route::get('/page/{name}', function ($name) {
    $page = \App\Models\Page::query()->where('name', $name)->firstOrFail();
    return (app()->getLocale() == 'ar' ? $page->content : ($page->content_en ?? $page->content));
})->middleware('localization');




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








Route::get('/update-user-type', [AgencyController::class, 'UpdateTypeUserAgency']);
Route::get('/update-user-cut-amount', [SalariesController::class, 'updateUserCutAmount']);
Route::get('/count-user-cut-amount', [SalariesController::class, 'countUserCutAmount']);




Route::get('admin/auth', function () {
        return view('checkLogin');
    })->name('admin/auth');
        Route::post('/authenticate', [\App\Admin\Controllers\GameChargeHistoryController::class, 'chickLogin'])->name('authenticate');



Route::get('/admin/agency-export-report', [
    \App\Admin\Controllers\ExportController::class,
    'usersAgencyTargets'
])->name('agency-export-report');


Route::get('/privacy-policy', function () {
    $page = \App\Models\Page::where("name", "privacy-policy")->first();
    return view('privacy.privacy', ['page' => $page]);
});



Route::get('/', [WelcomeController::class, 'index']);


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


Route::get('/delete_reward_target', function () {
    \Modules\Events\Entities\RewardTarget::query()->where('target', '=', '')->delete();
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


Route::get('x9b4-debug-track/{id}/{headerLog?}', function ($id, $headerLog = 'false') {
    $ids = explode(',', $id);
    settings()->set('debug_ids', $ids);
    settings()->set('header_log', filter_var($headerLog, FILTER_VALIDATE_BOOLEAN));
});

Route::get('x9b4-debug-track/update-wb', function () {
    settings()->set('bubble_frame_updated_at', time());
    settings()->set('wappel_frame_updated_at', time());
});



Route::get('/deeplink/{target?}', [\App\Http\Controllers\General\DeepLinkController::class, 'index']);
Route::get('/deeplink/{target?}', [\App\Http\Controllers\General\DeepLinkController::class, 'index']);




Route::get('/clean-gift-logs', [GiftLogController::class, 'cleanGiftLogsForAllUsers']);
Route::get('/remaining-diamonds', [GiftLogController::class, 'increaseMonthlyDiamond']);
Route::get('/users/sync-bd', [\App\Http\Controllers\Api\V1\UserController::class, 'syncBD']);




Route::group(['prefix' => 'paypal',], function () { //'middleware' => 'throttle:10,1'
    Route::get('/checkout/{id}', [PayPalController::class, 'checkout'])->name('paypal.checkout');
    Route::post('/create-order', [PayPalController::class, 'create'])->name('paypal.create');
});

Route::get('/total-room-gift', [GiftLogController::class, 'totalRoomGift']);

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

Route::get('/gift-logs-fix-total-diff/run', function (\Illuminate\Http\Request $request) {
    $chunk = (int) $request->query('chunk', 500);
    Artisan::call('gift-logs:fix-total-diff', ['--chunk' => $chunk]);
    return response()->json([
        'status'  => 'success',
        'message' => '✅ gift-logs:fix-total-diff executed successfully.',
        'output'  => Artisan::output(),
    ]);
});

















Route::view('/codapay-complete-landing', 'landing', ['title' => 'Complete Landing Page']);
Route::view('/codapay-atm-pending', 'landing', ['title' => 'ATM Pending Landing Page']);
Route::view('/codapay-pending-otc', 'landing', ['title' => 'Pending OTC Landing Page']);
Route::view('/codapay-subscription-notification', 'landing', ['title' => 'Subscription Notification Page']);












/*
if (config('app.debug')) {
    Route::post('/__debugbar/screen', function (\Illuminate\Http\Request $request) {
        Debugbar::info('Viewport:', $request->all());
        return response()->json(['ok' => true]);
    });
}
*/


Route::get('/sys/signal-flush', function () {
    $triggerFile = storage_path('framework/cache_flush_signal');
    if (!file_exists(dirname($triggerFile))) {
        @mkdir(dirname($triggerFile), 0775, true);
    }
    @touch($triggerFile);
    return response()->json(['status' => 'Signal file created']);
})->middleware('auth.basic');







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






  

Route::get('/update-user-monthly-diamonds/{id}', function ($id) {
    $userId = $id;
    $month = 4; // April
    $year = 2026;

    // Calculate total diamonds received from gift_logs for this month
    $totalDiamonds = \App\Models\GiftLog::where('receiver_id', $userId)
        ->whereMonth('created_at', $month)
        ->whereYear('created_at', $year)
        ->sum('giftPrice');

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
        'month' => $month,
        'year' => $year,
        'total_diamonds' => $totalDiamonds,
        'message' => 'تم تحديث مجموع الماسات الشهرية للمستخدم {$userId} بنجاح'
    ]);
});

use App\Models\Setting;


Route::get('/set-lucky-version-7', function () {

    $version = 4;

    Setting::updateOrCreate(
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
