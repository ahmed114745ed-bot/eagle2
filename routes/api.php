<?php

use App\Models\Room;
use App\Models\User;
use App\Enums\UserType;
use App\Helpers\Common;
use Illuminate\Support\Facades\Route;
use App\Jobs\AllOpeningRoomsZegoRequest;
use App\Admin\Controllers\WareController;
use App\Http\Controllers\PaySkyController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\VersionController;
use App\Http\Controllers\Api\V1\PkController;
use App\Http\Controllers\Api\V1\VipController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CoinController;
use App\Http\Controllers\Api\V1\GiftController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\PackController;
use App\Http\Controllers\Api\V1\RoomController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V2\MallController;
use App\Http\Controllers\Api\V1\AgoraController;
use App\Http\Controllers\Api\V1\ColorController;
use App\Http\Controllers\Api\V1\EmojiController;
use App\Http\Controllers\Api\V1\MusicController;
use App\Http\Controllers\Api\V1\ChargeController;
use App\Http\Controllers\Api\V1\FamilyController;
use App\Http\Controllers\Api\V2\AgencyController;
use App\Http\Controllers\Api\V1\AllGameController;
use App\Http\Controllers\Api\V1\CountryController;
use App\Http\Controllers\Api\V1\GiftLogController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RankingController;
use App\Http\Controllers\Api\V1\ExchangeController;
use App\Http\Controllers\Api\V1\QuestionController;
use App\Http\Controllers\Api\V1\Ranking2Controller;
use App\Http\Controllers\Api\V1\CommunityController;
use App\Http\Controllers\Api\V1\GroupChatController;
use App\Http\Controllers\Api\V1\BackgroundController;
use App\Http\Controllers\Api\V1\CoinReportController;
use App\Http\Controllers\Api\V1\MusicStoreController;
use App\Http\Controllers\Api\V1\ReportUserController;
use App\Http\Controllers\Api\V1\UploadLinkController;
use App\Http\Controllers\Api\V1\HomeCarouselController;
use App\Http\Controllers\Api\V1\RoomCategoryController;
use App\Http\Controllers\Api\v1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\GooglePaymentController;
use App\Http\Controllers\Api\V1\PaymentGetWayController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\Room\EnteranceController;
use App\Http\Controllers\Api\V1\Room\MicrophoneController;
use Modules\Achievement\Http\Controllers\AchievementController;
use Modules\Public\Http\Controllers\web\UpgradeLevelController;
use App\Http\Controllers\Api\V1\RequestBackgroundImageController;
use App\Http\Controllers\MallController as ControllersMallController;

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
        Log::info('game image ' . ' ' . @$user->nowGame?->image);
        $json = json_encode($d);
        dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json, $user->id, $room?->id, false), 'heavyProcessing');
        return "gooooooooooooooooooooooooooooood";
    });

    Route::post('update-room-count', [EnteranceController::class, 'updateRoomCountFromPusher']);

    Route::post('update-room-count-zego', [EnteranceController::class, 'updateRoomCountFromZego']);
    Route::post('fawry-callback', [PaymentMethodController::class, 'callback'])->middleware("verify.fawry.signature");

    Route::prefix('config')->group(function () {
        Route::post('app-check', [VersionController::class, 'versionAndCache']);
    });

    Route::get('/image-intro/{id}',[ UserController::class, 'image_intro']);
    Route::get('colors', [ColorController::class, 'index']);
    Route::get('all-servers', [RegisterController::class, 'all_servers']);

    // v2
    Route::prefix('search')->name('search.')->group(function () {
        Route::get('users', [UserController::class, 'search'])->name('users');
        Route::get('users2', [UserController::class, 'search2'])->name('users2');
        Route::get('users3', [UserController::class, 'userAgency'])->name('users3');
        Route::get('users4', [UserController::class, 'userFamily'])->name('users4');
        Route::get('app-manger', [UserController::class, 'userAgency'])->name('app-manger');
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


    Route::prefix('tickets')->middleware(['auth:sanctum', 'checkLatestToken', 'generalBan', 'userBan', 'throttle:4,1'])
        ->group(function () {
            Route::post('open', [\App\Http\Controllers\Api\V1\HomeController::class, 'openTicket']);
        });


    Route::post('/stripe-callback', [StripeController::class, 'handleWebhook']);

    // all route with auth
    Route::middleware(['auth:sanctum', 'checkLatestToken', 'generalBan', 'userBan'])->group(
        function () {

            Route::get('user-room', [UserController::class, 'userRoom']);

            Route::get('/agora-rtc-token', [AgoraController::class, 'RtcToken']);
            Route::post('/generate-upload-link', [UploadLinkController::class, 'uploadLink']);

            Route::post('/google-pay-purchased', [GooglePaymentController::class, 'purchasedFour']);

            Route::get('/countries/users', [CountryController::class, 'countries']);

            Route::get('/stripe-pay', [StripeController::class, 'pay']);
            Route::get('/paysky-pay', [PaySkyController::class, 'pay']);

            Route::get('zego-credential', [\App\Http\Controllers\Api\V1\UserController::class, 'zegoCredential']);


            Route::prefix('config')->group(function () {
                Route::post('keys-values', [\App\Http\Controllers\Api\V1\ConfigController::class, 'getConfigValues']);
                //                Route::post('app-check', [\App\Http\Controllers\VersionController::class, 'versionAndCache']);
            });
            Route::get('user-app-setting', [\App\Http\Controllers\Api\V1\UserController::class, 'app_setting']);

            Route::post('auth/logout', [\App\Http\Controllers\Api\V1\UserController::class, 'logout']);
            Route::post('/change-room-effect', [UserController::class, 'showSetting']);
            Route::get('get-users-support', [UserController::class, 'get_users_support']);
            Route::post('hide', [HomeController::class, 'hide']);
            Route::get('user-statistics', [\App\Http\Controllers\Api\V1\UserController::class, 'user_statistic']);

            // rooms api
            Route::prefix('rooms')->group(function () {
                Route::get('/room-user', [RoomController::class, 'userRooms']);
                Route::get('/', [RoomController::class, 'index']);
                Route::get('/game-rooms', [RoomController::class, 'gameRoom']);
                Route::post('/create', [RoomController::class, 'store']);
                Route::get('/{owner_id}/extra-data', [RoomController::class, 'extraRoomData']);
                Route::post('/{owner_id}/send-private-comment', [RoomController::class, 'sendPrivateComment']);
                Route::post('charge_dollar_for_owner', [ChargeController::class, 'charge_co_for_owner']);
                Route::post('{room_id}/disable-writing', [RoomController::class, 'disable_writing']);
                Route::post('pk/change-image', [RoomController::class, 'changeRoomImage']);
                Route::get('/{id}', [RoomController::class, 'show']);
                Route::post('/{id}/edit', [EnteranceController::class, 'update']);
                Route::post('firstOfRoom', [RoomController::class, 'firstOfRoom']);
                Route::post('admins', [RoomController::class, 'getAdmins']);
                Route::post('request-background-image', [RequestBackgroundImageController::class, 'RequestBackgroundImage']);
                Route::post('remove_pass', [RoomController::class, 'removeRoomPass']);
                Route::post('room_background_list', [BackgroundController::class, 'roomBackground']);
                Route::post('quit_room', [RoomController::class, 'quit_room']);
                Route::post('getRoomUsers', [RoomController::class, 'getRoomUsers']);
                Route::post('add_admin_to_room', [RoomController::class, 'is_admin']);
                Route::post('kick_out_of_room', [RoomController::class, 'out_room']);
                Route::post('remove_admin', [RoomController::class, 'remove_admin']);
                Route::post('black-list', [RoomController::class, 'blackList']);
                Route::post('remove-block', [RoomController::class, 'removeBlock']);
                Route::post('add-block', [RoomController::class, 'addBlock']);

                //Pk
                Route::middleware(['appFeatureEnable:pk'])->group(function () {
                    Route::post('create-pk', [PkController::class, 'createPK']);
                    Route::post('close-pk', [PkController::class, 'closePK']);
                    Route::post('show-pk', [PkController::class, 'showPK']);
                    Route::post('hide-pk', [PkController::class, 'hidePk']);
                });

                Route::middleware(['appFeatureEnable:pk'])->prefix('pk')->group(function () {
                    Route::post('create', [PkController::class, 'createPKWithoutZego']);
                    Route::post('close', [PkController::class, 'closePKWithoutZego']);
                    Route::post('show', [PkController::class, 'showPKWithoutZego']);
                    Route::post('hide', [PkController::class, 'hidePkWithoutZego']);
                });
                // Microphone

                Route::post('liveTime', [MicrophoneController::class, 'lifeTime']);
                Route::post('up-microphone', [MicrophoneController::class, 'upMicrophone']);
                Route::post('leave-microphone', [MicrophoneController::class, 'goMicrophone']);
                Route::post('mute_microphone', [MicrophoneController::class, 'mute_microphone']);
                Route::post('unmute_microphone', [MicrophoneController::class, 'unmute_microphone']);
                Route::post('lock_microphone_place', [MicrophoneController::class, 'shut_microphone']);
                Route::post('unlock_microphone_place', [MicrophoneController::class, 'open_microphone']);
                Route::post('enter_room', [EnteranceController::class, 'enter_room']);
            });
            Route::post('change_room_mode', [RoomController::class, 'changeMode']);
            Route::post('rooms/change-mic-mode', [RoomController::class, 'changeMicMode']);

            Route::prefix('coins')->group(function () {
                Route::get('/list', [CoinController::class, 'coinList']);
                Route::post('/buyCoins', [CoinController::class, 'buyCoins']);
                Route::get('/payment', [CoinController::class, 'paymentCoin']);
            });

            Route::prefix('users')->group(function () {
                Route::get('/{id}', [UserController::class, 'show'])->where('id', '[0-9]+');
                Route::get('/charger_agency', [\App\Http\Controllers\Api\V1\UserController::class, 'chargerAgency']);
            });

            Route::get('/room-countries', [RoomController::class, 'room_countries']);
            // end rooms api


            Route::prefix('account')->group(function () {
                Route::post('bind', [UserController::class, 'joinAccount']);
                Route::get('delete', [UserController::class, 'delete']);
                Route::post('change_phone', [\App\Http\Controllers\Api\V1\UserController::class, 'changePhone']);
                Route::post('change-phone-whatsapp', [UserController::class, 'changePhoneWhatsapp']);
                Route::post('reset-password-whatsapp', [UserController::class, 'resetWhatsapp']);
                Route::post('reset_password', [\App\Http\Controllers\Api\V2\Auth\ResetPasswordController::class, 'reset']);
            });

            Route::prefix('search')->group(function () {
                Route::get('/', [CommunityController::class, 'merge_search']);
                Route::get('user-friends', [CommunityController::class, 'user_friends']);
                Route::get('/history', [CommunityController::class, 'searchList']);
                Route::get('/clean_search_history', [CommunityController::class, 'cleanSearchList']);
            });

            Route::prefix('merge_search')->group(function () {
                Route::post('/', [CommunityController::class, 'merge_search']);
            });

            Route::prefix('community')->group(function () {
                Route::get('official_messages', [CommunityController::class, 'officialMessages']);
            });

            Route::prefix('home_carousels')->group(function () {
                Route::get('/', [HomeCarouselController::class, 'index']);
            });


            Route::prefix('families')->middleware(['appFeatureEnable:families'])->group(function () {
                Route::get('all', [FamilyController::class, 'index']);
                Route::get('show/{id}', [FamilyController::class, 'show']);
                Route::post('create', [FamilyController::class, 'store']);
                Route::post('ranking', [FamilyController::class, 'ranking']);
                Route::post('top-ranking', [FamilyController::class, 'topUserRanking']);
                Route::post('edit/{id}', [FamilyController::class, 'update']);
                Route::post('join', [FamilyController::class, 'join']);
                Route::get('delete/{id}', [FamilyController::class, 'destroy']);
                Route::post('remove_user', [FamilyController::class, 'removeUser']);
                Route::post('req_list', [FamilyController::class, 'req_list']);
                Route::post('take_action', [FamilyController::class, 'RequestFamilyAction']);
                Route::post('change_user_type', [FamilyController::class, 'changeFamilyUserType']);
                Route::post('getMembersList', [FamilyController::class, 'getMembersList']);
                Route::post('getFamilyRooms', [FamilyController::class, 'getFamilyRooms']);
                Route::post('exitFamily', [FamilyController::class, 'exitFamily']);
            });



            Route::post('charge_history', [ChargeController::class, 'chargeHistory']);
            Route::post('user-charge-coins', [ChargeController::class, 'userChargeCoins']);
            Route::post('user-charge-coinsII', [ChargeController::class, 'userChargeCoinsII']);

            Route::prefix('gifts')->withoutMiddleware('throttle')->group(function () {
                Route::get('/', [GiftController::class, 'index']);
                // Route::post('/send3', [GiftLogController::class, 'gift_queue_six2']);

                //todo
                Route::post('/send', [GiftLogController::class, 'gift_queue_cp']);
                Route::post('/send2', [GiftLogController::class, 'gift_queue_cp']);
                // Route::post('/send-lucky-gift', [GiftLogController::class, 'ofLucky']);
                Route::post('/send-lucky-gift-combo', [\App\Http\Controllers\Api\V1\GiftLogController::class, 'sendLuckyGift2'])->middleware(['checkCpu', 'appFeatureEnable:lucky']);
            });

            Route::get('my_gifts', [\App\Http\Controllers\Api\V1\GiftLogController::class, 'giftLogsList']);


            Route::prefix('group-chat')->group(function () {
                Route::get('/', [GroupChatController::class, 'index']);
                Route::post('/send', [GroupChatController::class, 'store']);
            });

            Route::prefix('countries')->group(function () {
                Route::get('/', [CountryController::class, 'allCountries']);
                Route::get('/{id}', [CountryController::class, 'getCountry']);
            });
            // user controller
            Route::get('user-agency-information', [UserController::class, 'user_agency_information']);


            Route::prefix('room_category')->group(function () {
                Route::get('classes', [RoomCategoryController::class, 'allClasses']);
                Route::get('types', [RoomCategoryController::class, 'getTypes']);
                Route::get('types_by_class/{id}', [RoomCategoryController::class, 'getClassChildren']);
            });

            Route::prefix('backgrounds')->group(function () {
                Route::get('/', [BackgroundController::class, 'allBackgrounds']);
                Route::get('/me', [BackgroundController::class, 'allMyBackgrounds']);
            });

            Route::prefix('user_info')->group(function () {

                Route::get('my_pack', [PackController::class, 'my_pack']);
                Route::post('use_pack_item', [PackController::class, 'usePackItem']);
                Route::post('takeOff', [PackController::class, 'takeOff']);
                //                Route::get('my_store', [UserController::class, 'my_store']);
                //                Route::get('my_income', [UserController::class, 'my_income']);
                Route::post('getTimes', [HomeController::class, 'getTimes']);
            });

            Route::prefix('exchange')->group(function () {
                Route::get('/list', [ExchangeController::class, 'exchangeList']);
                Route::post('/make', [ExchangeController::class, 'exchangeSave']);
                Route::get('/logs', [ExchangeController::class, 'exchangeLogs']);
            });

            Route::get('trxs', [ChargeController::class, 'trxLog']);
            Route::get('images', [HomeController::class, 'getImages']);

            Route::post('check_wapel', [HomeController::class, 'check_wapel']);

            Route::get('getUserHides', [HomeController::class, 'getUserHides']);

            // user api
            Route::get('my-data', [UserController::class, 'my_data']);


            Route::get('explain-invitation', [\App\Http\Controllers\Api\V1\UserController::class, 'explain_invitation'])->name('create-code-invitation');
            Route::get('parent-statistic', [\App\Http\Controllers\Api\V1\UserController::class, 'UserEarnFromInvitationStatistics']);
            Route::get('parent-user', [\App\Http\Controllers\Api\V1\UserController::class, 'parentUser']);
            Route::get('user-earn-from-invitation', [\App\Http\Controllers\Api\V1\UserController::class, 'UserEarnFromInvitation']);
            Route::get('create-code-invitation', [\App\Http\Controllers\Api\V1\UserController::class, 'CreateCodeInvitation']);
            Route::get('add-code-invitation', [\App\Http\Controllers\Api\V1\UserController::class, 'AddCodeInvitation']);
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
                Route::post('/', [RankingController::class, 'ranking']);
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
            Route::prefix('vips')->middleware(['appFeatureEnable:vips'])->group(function () {
                Route::get('/list', [VipController::class, 'vipList']);
                Route::post('/buyVip', [VipController::class, 'buyVip']);
                Route::post('/buy-vip-percentage', [ControllersMallController::class, 'buyVip']);
                Route::post('/use', [VipController::class, 'vip_use']);
                Route::post('/send-to-user', [VipController::class, 'vip_send']);
            });
            Route::get('levels/badges', [VipController::class, 'badges']);
            Route::get('levels', [VipController::class, 'index']);
            Route::get('profile-frame-wares', [\App\Http\Controllers\Api\V1\WareController::class, 'profile_frame_wares']);
            // end vips



            Route::prefix('emojis')->group(function () {
                Route::get('/', [EmojiController::class, 'index']);
                Route::get('/{id}', [EmojiController::class, 'show']);
            });
            // start levels
            Route::get('levels-ranges', [UpgradeLevelController::class, 'getLevelsRange']);
            // end levels
            Route::prefix('mall')->middleware(['appFeatureEnable:mall'])->group(function () {
                Route::get('wares', [MallController::class, 'index']);
                Route::post('buy', [MallController::class, 'buyWare']);
                Route::post('send', [MallController::class, 'sendWare']);
                Route::get('best-sale', [MallController::class, 'bestWareSale']);
            });
            //start games
            Route::prefix('all-games1')->group(function () {
                Route::get('/', [AllGameController::class, 'index']);
                Route::post('update-game', [AllGameController::class, 'updateGame']);
            });
            // end games

            // questions
            Route::get('questions', [QuestionController::class, 'questions']);
            Route::post('send-mail-to-customer-service', [QuestionController::class, 'send_mail_to_customer_service']);
            // end questions

            Route::prefix('agencies')->middleware(['appFeatureEnable:agencies'])->group(function () {
                Route::post('charge_co_for_users', [ChargeController::class, 'sendMoneyFoeHost']);
                Route::get('charge_co_for_usersHistory', [ChargeController::class, 'chargeCoForUsersHistory']);
                Route::post('charge_dollar_for_owner', [ChargeController::class, 'ChargeDollarForOwner']);
                Route::get('charge_dollar_for_OwnerHistory', [ChargeController::class, 'chargeDollarHistory']);
                Route::post('join_request', [AgencyController::class, 'joinRequest']);
                Route::get('show', [AgencyController::class, 'view']);
                Route::post('showAllusers', [AgencyController::class, 'agencyMembers']);
                Route::get('show-agency-request', [AgencyController::class, 'showAgencyRequest']);
                Route::get('show_request', [AgencyController::class, 'show_request']);
                Route::post('actions_request', [AgencyController::class, 'Accept_request']);
                Route::get('list_options_his', [AgencyController::class, 'list_options_his']);
                Route::post('historyAgancy', [AgencyController::class, 'historyAgencySearch']);
                Route::post('make-user-as-operator', [AgencyController::class, 'make_user_handling_requests']);
                Route::post('charge_to', [ChargeController::class, 'chargeTo']);
                Route::post('{id}', [AgencyController::class, 'update'])->where('id', '[0-9]+');
                Route::get('charges', [AgencyController::class, 'agenciesCharge']);
            });
            Route::prefix('payment-gateway')->group(function () {
                Route::get('/', [PaymentGetWayController::class, 'index']);
                Route::post('/select-payment-get-way', [PaymentGetWayController::class, 'selectPaymentGateway']);
            });

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
                Route::post('/create', [MusicController::class, 'store']);
            });
        }
    );
});
