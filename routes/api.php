<?php

use App\Events\PublicTestEvent;
use App\Helpers\Common;
use App\Http\Controllers\Api\BadgeController;
use App\Http\Controllers\Api\CountriesInPolygonController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\V1\AgoraController;
use App\Http\Controllers\Api\V1\AllGameController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChargeController;
use App\Http\Controllers\Api\V1\ChargeLevelController;
use App\Http\Controllers\Api\V1\CoinController;
use App\Http\Controllers\Api\V1\CoinReportController;
use App\Http\Controllers\Api\V1\ColorController;
use App\Http\Controllers\Api\V1\CommunityController;
use App\Http\Controllers\Api\V1\ConfigController;
use App\Http\Controllers\Api\V1\CountryController;
use App\Http\Controllers\Api\V1\EmojiController;
use App\Http\Controllers\Api\V1\GiftCategoryController;
use App\Http\Controllers\Api\V1\GiftController;
use App\Http\Controllers\Api\V1\GiftLogController;
use App\Http\Controllers\Api\V1\GooglePaymentController;
use App\Http\Controllers\Api\V1\HomeCarouselController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\MusicController;
use App\Http\Controllers\Api\V1\PackController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\QuestionController;
use App\Http\Controllers\Api\V1\Ranking2Controller;
use App\Http\Controllers\Api\V1\RankingController;
use App\Http\Controllers\Api\V1\ReportUserController;
use App\Http\Controllers\Api\V1\StorageUploadController;
use App\Http\Controllers\Api\V1\UploadLinkController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V2\MallController;
use App\Http\Controllers\AppFeatureController;
use App\Http\Controllers\Dashboard\StatisticsController;
use App\Http\Controllers\FirebaseAuthController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\NowPaymentsController;
use App\Http\Controllers\PaySkyController;
use App\Http\Controllers\PaytabsController;
use App\Http\Controllers\RoomSettingController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\TestDiamondController;
use App\Http\Controllers\VersionController;
use App\Jobs\AllOpeningRoomsZegoRequest;
use App\Models\User;
use App\Services\CodapayService;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Utd\AreaManager\Http\Controllers\AreaManagerController;
use Modules\Public\Http\Controllers\web\UpgradeLevelController;
use Utd\Achievements\Http\Controllers\AchievementController;
use Utd\Family\Http\Controllers\Api\FamilyController;
use Utd\Room\Entities\Room;

Route::get('/health', [HealthCheckController::class, 'status']);
Route::get('/badges', [BadgeController::class, 'index']);
Route::post('/now-payments-callback', [NowPaymentsController::class, 'paymentCallback']);
Route::post('agora-webhook', [AgoraController::class, 'webhook']);
Route::post('/check-phone', [UserController::class, 'checkPhone']);
Route::prefix(config('app.api_prefix'))->group(function () {
    Route::get('test-game-rtm', function () {

        $user = User::find(524);
        $room      = Room::withoutAppends()->select(['id'])->where("uid", $user->now_room_uid)->first();

        $d    = [
            "messageContent" => [
                "message" => "SBG",
                'uImage'  => $user->profile?->avatar ?? 0,
                'uName'   => $user->name ?? '',
                'uId'     => $user->id ?? 0,
                'coins'   => 50000,
                "gImage"  => @$user->nowGame?->image
            ]
        ];
        $json = json_encode($d);
        dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json, $user->id, $room?->id, false), 'heavyProcessing');
        return "gooooooooooooooooooooooooooooood";
    });

    Route::post('fawry-callback', [PaymentMethodController::class, 'callback'])->middleware("verify.fawry.signature");
    Route::post('Utd-fawry-callback', [PaymentMethodController::class, 'utdCallback'])->middleware("verify.utdFawry.signature");
    Route::get('/fawry/done', [PaymentMethodController::class, 'success']);
    Route::post('paypal-callback', [PayPalService::class, 'callback'])->name('paypal.callback')->middleware(['verify.paypal.webhook']);
    Route::get('paypal-return/{orderId}', [PayPalService::class, 'success'])->name('paypal.success');
    Route::get('paypal-cancel/{orderId}', [PayPalService::class, 'cancel'])->name('paypal.cancel');

    Route::get('codapay-callback', [CodapayService::class, 'callback'])->name('codapay.callback')->middleware(['verify.codapay.webhook']);
    Route::get('codapay-success/{id}/{country}', [CodapayService::class, 'success'])->name('codapay.success');

    Route::prefix('config')->group(function () {
        Route::post('app-check', [VersionController::class, 'versionAndCache']);
    });

    Route::post('/chatVideo', [StorageUploadController::class, 'chatVideo']);
    Route::get('/image-intro/{id}', [UserController::class, 'image_intro']);
    Route::get('colors', [ColorController::class, 'index']);
    Route::get('colors/v2', [ColorController::class, 'appCollor']);
    Route::get('all-servers', [RegisterController::class, 'all_servers']);

    // v2
    Route::prefix('search')->name('search.')->group(function () {
        Route::get('users', [UserController::class, 'search'])->name('users');
        Route::get('users2', [UserController::class, 'search2'])->name('users2');
        Route::get('owner-rooms', [UserController::class, 'searchOwnerRoomWithPage'])->name('owner-rooms');
        Route::get('users7', [UserController::class, 'usersAudioRoom'])->name('users7');
        Route::get('users8', [UserController::class, 'usersLiveRoom'])->name('users8');
        Route::get('users-bd', [UserController::class, 'user_bd'])->name('users-bd');
        Route::get('users-bd2', [UserController::class, 'user_bd2'])->name('users-bd2');
        Route::get('users-bd-by-countries', [UserController::class, 'userBdByCountries'])->name('users-bd-by-countries');
        Route::get('users-superadmin', [UserController::class, 'superAdminUsers'])->name('users-superadmin');
        Route::get('users-subsuperadmin', [UserController::class, 'subSuperAdminUsers'])->name('users-subsupeadmin');
        Route::get('users-areamanager', [UserController::class, 'subAreaManager'])->name('users-areamanager');
        Route::get('users-superadmin2', [UserController::class, 'superAdminUsers2'])->name('users-superadmin2');
        Route::get('area-manager', [AreaManagerController::class, 'areaManger'])->name('area-manager');
        Route::get('users-by-country', [UserController::class, 'usersByCountry'])->name('users-superadmin.country');
        Route::get('users-by-countries', [UserController::class, 'usersByCountries'])->name('users-by-countries');
        Route::get('users3', [UserController::class, 'userAgency'])->name('users3');
        Route::get('users4', [FamilyController::class, 'userFamily'])->name('users4');
        Route::get('users5', [UserController::class, 'userAgencyShipping'])->name('users5');
        Route::get('app-manger', [UserController::class, 'userAgency'])->name('app-manger');
        Route::get('agencies', [UserController::class, 'agencies'])->name('agencies');
        Route::get('superadmin-agencies', [UserController::class, 'superAdminAgencies'])->name('superadmin-agencies');
        Route::get('host-agency', [UserController::class, 'hostAgencies'])->name('hostAgency');
        Route::get('charges', [UserController::class, 'charges'])->name('charges');
        Route::get('countries', [CountryController::class, 'searchCountries'])->name('countries');
        Route::get('regions', [CountryController::class, 'searchRegions'])->name('regions');
        Route::get('language', [LanguageController::class, 'searchLanguage'])->name('language');
        Route::get('get-country-users', [UserController::class, 'bdCountryUsers'])->name('country-users');
        Route::get('users-area-manager', [UserController::class, 'usersAreaManager'])->name('users-area-manager');
    });

    // authorization
    Route::prefix('auth')->group(function () {
        Route::get('all-countries', [CountryController::class, 'index']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('recall-account', [AuthController::class, 'recallAccount']);
        Route::post('forget_password', [\App\Http\Controllers\Api\V2\Auth\ForgotPasswordController::class, 'reset']);
        Route::post('verify-code', [\App\Http\Controllers\Api\V2\Auth\ForgotPasswordController::class, 'verifyCode']);
    });


    Route::prefix('tickets')->middleware(['auth:sanctum', 'checkLatestToken', 'generalBan', 'userBan', 'throttle:10,1'])
        ->group(function () {
            Route::post('open', [\App\Http\Controllers\Api\V1\HomeController::class, 'openTicket']);
        });


    Route::post('/stripe-callback', [StripeController::class, 'handleWebhook']);
    Route::get('/payment/success', [StripeController::class, 'success']);
    Route::get('/payment/cancel', [StripeController::class, 'cancel']);


    // all route with auth
    Route::middleware(['auth:sanctum', 'checkLatestToken', 'generalBan', 'userBan', 'update.last.seen', 'localization'])->group(
        function () {
            // Route::post('/broadcasting/auth', function (Request $request) {
            //     return Broadcast::auth($request);
            // });
            Route::post('/broadcasting/auth', function (Request $request) {
                try {
                    $authResponse = Broadcast::auth($request);
                    return $authResponse;
                } catch (\Exception $e) {
//                    return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
                    return response()->json([
                        'success' => false,
                        'type' => get_class($e),
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                    ], 500);
                }
            });

            Route::get('/user-gifts', [UserController::class, 'userGifts']);

            Route::get('user-room', [UserController::class, 'userRoom']);

            Route::get('/agora-rtc-token', [AgoraController::class, 'RtcToken']);
            Route::post('/generate-upload-link', [UploadLinkController::class, 'uploadLink']);

            Route::post('/google-pay-purchased', [GooglePaymentController::class, 'purchasedFour']);
            Route::post('/testCharge', [GooglePaymentController::class, 'addChargeLevel']);

            Route::get('/countries/users', [CountryController::class, 'countries']);

            Route::get('/stripe-pay', [StripeController::class, 'pay']);
            Route::get('/paysky-pay', [PaySkyController::class, 'pay']);

            Route::get('zego-credential', [UserController::class, 'zegoCredential']);


            Route::prefix('config')->group(function () {
                Route::get('settings', [VersionController::class, 'settings']);
                Route::post('keys-values', [ConfigController::class, 'getConfigValues']);
                //                Route::post('app-check', [\App\Http\Controllers\VersionController::class, 'versionAndCache']);
            });
            Route::get('user-app-setting', [UserController::class, 'appSetting']);

            Route::post('auth/logout', [UserController::class, 'logout']);
            Route::post('/change-room-effect', [UserController::class, 'showSetting']);
            Route::get('get-users-support', [UserController::class, 'get_users_support']);
            Route::post('hide', [HomeController::class, 'hide']);
            Route::get('user-statistics', [UserController::class, 'user_statistic']);
            Route::get('user-levels', [UserController::class, 'userLevels']);

            Route::post('/firebase/custom-token', [FirebaseAuthController::class, 'loginWithUid']);
            Route::prefix('coins')->group(function () {
                Route::get('/list', [CoinController::class, 'coinList']);
                Route::post('/buyCoins', [CoinController::class, 'buyCoins']);
                Route::get('/payment', [CoinController::class, 'paymentCoin']);
                Route::get('user-report', [CoinController::class, 'userCoinReport']);
                Route::get('shipping-agency-report', [CoinController::class, 'shippingAgencyCoinReport']);
            });

            Route::prefix('users')->group(function () {
                Route::get('/{id}', [UserController::class, 'show'])->where('id', '[0-9]+');
                Route::get('/details', [UserController::class, 'showUsersDetails'])->where('id', '[0-9]+');
                Route::get('v2/{id}', [UserController::class, 'vTwoshow'])->where('id', '[0-9]+');
                Route::get('/charger_agency', [UserController::class, 'chargerAgency']);
                Route::get('/play', [UserController::class, 'allUsersPlayGame']);
                Route::get('/stop-play', [UserController::class, 'updateGame']);
                Route::get('/online', [UserController::class, 'online']);
                Route::get('/friends', [UserController::class, 'friends']);
                Route::get('/data', [UserController::class, 'dataUser']);

                Route::get('/stats/{id?}', [UserController::class, 'stats']);
                Route::get('/rooms/{id?}', [UserController::class, 'rooms']);
                Route::get('/vip-level/{id?}', [UserController::class, 'vipLevel']);
                Route::get('/frames/{id?}', [UserController::class, 'frames']);
            });


            Route::prefix('account')->group(function () {
                Route::post('bind', [UserController::class, 'joinAccount']);
                Route::get('delete', [UserController::class, 'delete']);
                Route::post('change_phone', [UserController::class, 'changePhone']);
                Route::post('change-phone-whatsapp', [UserController::class, 'changePhoneWhatsapp']);
                Route::post('reset-password-whatsapp', [UserController::class, 'resetWhatsapp']);
                Route::post('reset_password', [\App\Http\Controllers\Api\V2\Auth\ResetPasswordController::class, 'reset']);
            });

            Route::prefix('search')->group(function () {
                //                Route::get('/', [CommunityController::class, 'merge_search']);
                Route::get('/', [CommunityController::class, 'mergeSearchV2']);
                Route::get('user-friends', [CommunityController::class, 'user_friends']);
                Route::get('/history', [CommunityController::class, 'searchList']);
                Route::get('/clean_search_history', [CommunityController::class, 'cleanSearchList']);
            });

            Route::prefix('merge_search')->group(function () {
                Route::post('/', [CommunityController::class, 'merge_search']);
            });

            Route::prefix('community')->group(function () {
                Route::get('official_messages', [CommunityController::class, 'officialMessages']);
                Route::get('notifications', [CommunityController::class, 'notifications']);
            });

            Route::prefix('home_carousels')->group(function () {
                Route::get('/', [HomeCarouselController::class, 'index']);
            });

            Route::post('charge_history', [ChargeController::class, 'chargeHistory']);
            Route::post('user-charge-coins', [ChargeController::class, 'userChargeCoins']);
            Route::post('user-charge-coinsII', [ChargeController::class, 'userChargeCoinsII']);

            Route::prefix('countries')->group(function () {
                Route::get('/', [CountryController::class, 'allCountries']);
                Route::get('/categories', [CountryController::class, 'countryCategory']);
                Route::get('/{id}', [CountryController::class, 'getCountry']);
                Route::get('/{id}/html', [CountryController::class, 'getCountryByHtml']);
                Route::post('change-request', [CountryController::class, 'changeRequest']);
            });
            // user controller

            Route::prefix('user_info')->group(function () {

                Route::get('my_pack', [PackController::class, 'my_pack']);
                Route::post('use_pack_item', [PackController::class, 'usePackItem']);
                Route::post('takeOff', [PackController::class, 'takeOff']);
                Route::post('takeOffV2', [PackController::class, 'takeOffV2']);
                //                Route::get('my_store', [UserController::class, 'my_store']);
                //                Route::get('my_income', [UserController::class, 'my_income']);
                Route::post('getTimes', [HomeController::class, 'getTimes']);
            });
            Route::post('send_pack', [UserController::class, 'sendPack']);

            Route::get('trxs', [ChargeController::class, 'trxLog']);
            Route::get('images', [HomeController::class, 'getImages']);

            Route::post('check_wapel', [HomeController::class, 'check_wapel']);

            Route::get('getUserHides', [HomeController::class, 'getUserHides']);

            // user api
            Route::get('my-data', [UserController::class, 'my_data']);
            Route::post('update-user-image/{image_id}', [UserController::class, 'update_user_multi_images']);


            Route::get('explain-invitation', [UserController::class, 'explain_invitation'])->name('create-code-invitation');
            Route::get('parent-statistic', [UserController::class, 'UserEarnFromInvitationStatistics']);
            Route::get('parent-user', [UserController::class, 'parentUser']);
            Route::get('user-earn-from-invitation', [UserController::class, 'UserEarnFromInvitation']);
            Route::get('create-code-invitation', [UserController::class, 'CreateCodeInvitation']);
            Route::get('add-code-invitation', [UserController::class, 'AddCodeInvitation']);
            Route::get('/invitations/earnings', [UserController::class, 'invitationsEarnings']);
            Route::post('/invitations/earnings/{id}/claim', [UserController::class, 'invitationsEarningsClaim']);

            // Todo Refact
            Route::get('my-store', [UserController::class, 'my_store_all']);

            Route::prefix('profile')->group(function () {
                Route::get('get/{id}', [ProfileController::class, 'show']);
                Route::post('update', [ProfileController::class, 'update']);
                Route::get('visitors', [ProfileController::class, 'myProfileVisitorsList']);
                Route::post('liked', [ProfileController::class, 'liked']);
                Route::post('ignored', [ProfileController::class, 'ignored']);
                Route::get('users', [ProfileController::class, 'getNearbyUsers']);
                Route::get('related', [ProfileController::class, 'related']);
                Route::get('following', [ProfileController::class, 'getFollowingUsers']);
            });

            // TODO refact @eriny
            Route::prefix('relations')->group(function () {
                Route::get('/', [UserController::class, 'userFriend']);
                Route::post('follow', [UserController::class, 'follow']);
                Route::post('un-follow', [UserController::class, 'unFollow']);
                Route::post('is_user_friend', [HomeController::class, 'check_if_friend']);
                Route::post('report_user', [ReportUserController::class, 'ReportUser']);
            });
            // end user api

            //start rankin
            Route::prefix('ranking')->group(function () {
                Route::post('/', [RankingController::class, 'ranking2']);
                Route::post('/version3', [RankingController::class, 'rankingV2']);
                Route::post('/version2', [RankingController::class, 'rankingV2']);
                Route::post('/room', [UserController::class, 'ranking_room']);
                Route::get('/top_user_ranking', [RankingController::class, 'topUserRanking']);
                Route::post('/one-room', [RankingController::class, 'oneRoomRanking']);


                //v2
                Route::post('/v2', [Ranking2Controller::class, 'ranking']);
                Route::post('/v2/room', [Ranking2Controller::class, 'ranking_room']);
                Route::get('/v2/top_user_ranking', [Ranking2Controller::class, 'topUserRanking']);
                Route::post('/v2/one-room', [Ranking2Controller::class, 'oneRoomRanking']);
            });
            // end ranking

            // start vips
            Route::get('profile-frame-wares', [\App\Http\Controllers\Api\V1\WareController::class, 'profile_frame_wares']);
            // end vips



            Route::prefix('emojis')->group(function () {
                Route::get('/categories', [EmojiController::class, 'categories']);
                Route::get('/', [EmojiController::class, 'index']);
                Route::get('/v2', [EmojiController::class, 'all']);
                Route::get('/{id}', [EmojiController::class, 'show']);
            });

            Route::prefix('/v2/emojis')->group(function () {
                Route::get('/categories', [EmojiController::class, 'categories']);
                Route::get('/', [EmojiController::class, 'all']);
            });
            // start levels
            Route::get('levels-ranges', [UpgradeLevelController::class, 'getLevelsRange']);
            // end levels
            Route::prefix('mall')->middleware(['appFeatureEnable:mall'])->group(function () {
                Route::get('wares', [MallController::class, 'index']);
                Route::get('padding', [MallController::class, 'padding']);
                Route::post('buy', [MallController::class, 'buyWare']);
                Route::post('send', [MallController::class, 'sendWare']);
                Route::get('best-sale', [MallController::class, 'bestWareSale']);

                Route::get('wabble', [MallController::class, 'wabbleWare']);
                Route::get('wabbleAll', [MallController::class, 'wabbleAll']);
            });
            //start games
            Route::prefix('all-games1')->group(function () {
                Route::get('/', [AllGameController::class, 'index']);
                Route::get('/v2/out-of-room', [AllGameController::class, 'outRoom']);
                Route::get('/v2/in-room', [AllGameController::class, 'inRoom']);
                Route::post('update-game', [AllGameController::class, 'updateGame']);
            });
            // end games

            // questions
            Route::get('questions', [QuestionController::class, 'questions']);
            Route::post('send-mail-to-customer-service', [QuestionController::class, 'send_mail_to_customer_service']);
            // end questions


            Route::get('/charge-level', [ChargeLevelController::class, 'chargeLevel']);

            // coins reports
            Route::get('/coin-reports', [CoinReportController::class, 'index']);
            Route::get('/event-coin-reports', [CoinReportController::class, 'eventCoins']);
            // end coin report
            Route::post('un_hide', [\App\Http\Controllers\Api\V1\HomeController::class, 'un_hide']);




            Route::prefix('banners')->group(function () {
                Route::get('/', [\App\Http\Controllers\BannerController::class, 'index2']);
                // Route::get('/', [\App\Http\Controllers\BannerController::class, 'index']);
                // Route::get('/banner', [\App\Http\Controllers\BannerController::class, 'index2']);
            });

            Route::prefix('black_list')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\V1\BlackListController::class, 'index']);
                Route::post('/add', [\App\Http\Controllers\Api\V1\BlackListController::class, 'add']);
                Route::post('/remove', [\App\Http\Controllers\Api\V1\BlackListController::class, 'remove']);
                Route::get('/check/{userId}', [\App\Http\Controllers\Api\V1\BlackListController::class, 'checkBlockStatus']);
            });

            // Route::get('/data-data', function(){
            //     $id = \App\Models\User::first()?->id;
            //     $data = Common::level_center(@$id);
            //     return $data;
            // });

            Route::post('test-google-id', [AuthController::class, 'verifyGoogleToken']);

            // Music Store
            // Route::prefix('music')->group(function () {
            //     Route::get('/', [MusicStoreController::class, 'index']);
            //     Route::post('/', [MusicStoreController::class, 'store']);
            // });
            Route::get('achievement-valid-images', [AchievementController::class, 'achievement_valid_images']);

            Route::prefix('music')->group(function () {
                Route::get('/all', [MusicController::class, 'index']);
                Route::get('/user', [MusicController::class, 'userMusic']);
                Route::delete('{id}/user', [MusicController::class, 'destroyUserMusic']);
                Route::post('/create', [MusicController::class, 'store']);
            });

            Route::get('app_feature', [AppFeatureController::class, 'show']);
            Route::get('room_settings', [RoomSettingController::class, 'show']);

            Route::group(['prefix' => 'paytabs', 'as' => 'paytabs.'], function () {
                Route::any('pay', [PaytabsController::class, 'payment'])->name('pay');
                // Route::any('callback', [PaytabsController::class, 'callback'])->name('callback');
                Route::any('response', [PaytabsController::class, 'response'])->name('response');
            });

            Route::get('/public-test/{ids}', function ($ids) {
                $title = 'System‑wide Test';
                $body  = 'This is only a test.';

                $idArray = explode(',', $ids);

                $tokens = User::whereNotNull('notification_id')
                    ->whereIn('id', $idArray)
                    ->pluck('notification_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();

                return Common::send_firebase_notification($tokens, $title, $body);
            });

            Route::get('/send_test_notifications', function () {

                $notificationTokens = DB::table('users')->orderBy('id', 'desc')->limit(100)->pluck('notification_id')->filter()->toArray();

                $title = 'Test';
                $body = 'Test';

                Common::send_firebase_notification($notificationTokens, $title, $body, '', [], 'vip');

                return "notification sent successfully!";
            });

            Route::get('/unsubscribe-all-from-topic/{topic}', function ($topic) {
                $tokens = User::whereNotNull('notification_id')
                    ->pluck('notification_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();
                // $result = Common::unsubscribeFromTopic($tokens, $topic);

                // return response()->json($result);
            });
        }
    );

    Route::get('/languages', [LanguageController::class, 'index']);

    Route::get('/privacy-policy', function () {
        $Page = \App\Models\Page::where("name", "privacy-policy")->first();
        return response()->json(['html' => $Page]);
    });
});

Route::match(['get', 'post'], '/paytabs/callback', [PayTabsController::class, 'callback'])->name('paytabs.callback');
Route::match(['get', 'post'], '/paytabs/return/{payment_id}', [PayTabsController::class, 'return'])->name('paytabs.return');




Route::get('/public-official-test/{ids}', function ($ids) {
    $title    = 'System‑wide Test';
    $body_en  = 'This is only a test.';
    $body_ar  = 'هذا مجرد اختبار.';
    $image    = null; // مثال: 'https://example.com/image.jpg'
    $data     = null; // يجب أن يكون string|null
    $subType  = null;
    $type     = 2;
    $fromUser = null;

    $idArray = explode(',', $ids);

    $users = User::whereIn('id', $idArray)
        ->get();

    foreach ($users as $user) {
        Common::sendOfficialMessage(
            $user->id,
            $body_en,
            $title,
            $type,
            $subType,
            $body_ar,
            $image,
            $fromUser
        );
    }

    return response()->json(['message' => 'تم إرسال الإشعارات بنجاح']);
});

Route::get('gifts-by-id', function (Request $request) {
    $gift = \Utd\Gifts\Entities\Gift::find($request->get('id'));
    if (!$gift) {
        return response()->json([]);
    }

    $imageUrl = $gift->show_img
        ?? ($gift->show_img ? Storage::url($gift->show_img) : null);

    return response()->json([
        'id'    => $gift->id,
        'name'  => $gift->name,
        'image' => $imageUrl,
    ]);
});

Route::get('/moment-contract-test', function (\App\Contracts\MomentContract $moment) {
    return response()->json([
        'resolved_class' => get_class($moment),
        'data' => $moment->getMomentsByType(1, 1206, 1, null),
    ]);
});

Route::post('/countries-in-polygon', [CountriesInPolygonController::class, 'getCountriesInPolygon']);
Route::get('dashboard/summary', [StatisticsController::class, 'summary']);
Route::get('dashboard/charts', [StatisticsController::class, 'charts']);
Route::get('dashboard/top-rooms', [StatisticsController::class, 'topRooms']);
