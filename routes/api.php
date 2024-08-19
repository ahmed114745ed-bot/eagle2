<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\PkController;
use App\Http\Controllers\Api\V1\VipController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\MallController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RankingController;
use App\Http\Controllers\Api\V1\RequestBackgroundImageController;
// use App\Http\Controllers\Api\V1\Room\PKController;
use App\Http\Controllers\Api\V1\RoomCategoryController;
use App\Http\Controllers\Api\V1\RoomController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ColorController;
use App\Http\Controllers\Api\V1\ChargeController;
use App\Http\Controllers\Api\V1\FamilyController;
use App\Http\Controllers\Api\V1\CountryController;
use App\Http\Controllers\Api\V1\ExchangeController;
// use App\Http\Controllers\Api\V1\Room\PKController;
use App\Http\Controllers\Api\V1\CommunityController;
use App\Http\Controllers\Api\V1\GroupChatController;
use App\Http\Controllers\Api\V1\BackgroundController;

use App\Http\Controllers\Api\V1\Room\EnteranceController;
use App\Http\Controllers\Api\V1\Room\MicrophoneController;
use Modules\Public\Http\Controllers\web\UpgradeLevelController;

Route::prefix(config('app.api_prefix'))->group(function () {

    Route::post('update-room-count', [EnteranceController::class, 'updateRoomCountFromPusher']);

    Route::post('update-room-count-zego', [EnteranceController::class, 'updateRoomCountFromZego']);

    Route::prefix('config')->group(function () {
        Route::post('app-check', [\App\Http\Controllers\VersionController::class, 'versionAndCache']);
    });

    Route::get('colors',[ColorController::class,'index']);
    Route::get('all-servers', [\App\Http\Controllers\Api\v1\Auth\RegisterController::class, 'all_servers']);

    // v2
    Route::prefix('search')->name('search.')->group(function () {
        Route::get('users', [\App\Http\Controllers\Api\v1\UserController::class, 'search'])->name('users');
        Route::get('users2', [\App\Http\Controllers\Api\v1\UserController::class, 'search2'])->name('users2');
        Route::get('users3', [\App\Http\Controllers\Api\v1\UserController::class, 'userAgency'])->name('users3');
        Route::get('app-manger', [\App\Http\Controllers\Api\v1\UserController::class, 'userAgency'])->name('app-manger');
    });

    // authorization
    Route::prefix('auth')->group(function () {
        Route::get('all-countries', [CountryController::class, 'index']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('recall-account', [AuthController::class, 'recallAccount']);

    });

    // all route with auth
    Route::middleware(['auth:sanctum', 'checkLatestToken', 'generalBan','userBan'])->group(
        function () {
            // rooms api
            Route::prefix('rooms')->group(function () {
                Route::get('/', [RoomController::class, 'index']);
                Route::post('/create', [RoomController::class, 'store']);
                Route::get('/{owner_id}/extra-data', [RoomController::class, 'extraRoomData']);
                Route::post('/{owner_id}/send-private-comment', [RoomController::class, 'sendPrivateComment']);
                Route::post('charge_dollar_for_owner', [ChargeController::class, 'charge_co_for_owner']);
                Route::post('{room_id}/disable-writing', [RoomController::class, 'disable_writing']);
                Route::post('pk/change-image', [RoomController::class, 'changeRoomImage']);
                Route::get('/{id}', [RoomController::class, 'show']);
                Route::post('firstOfRoom', [RoomController::class, 'firstOfRoom']);
                Route::post('admins', [RoomController::class, 'getAdmins']);
                Route::post('request-background-image', [RequestBackgroundImageController::class, 'RequestBackgroundImage']);
                Route::post('remove_pass', [RoomController::class, 'removeRoomPass']);
                Route::post('room_background_list', [BackgroundController::class, 'roomBackground']);
                Route::post('quit_room', [RoomController::class, 'quit_room']);
                Route::post('getRoomUsers', [RoomController::class, 'getRoomUsers']);
                Route::post('add_admin_to_room', [RoomController::class, 'is_admin']);

                //Pk
                Route::post('create-pk', [PkController::class, 'createPK']);
                Route::post('close-pk', [PkController::class, 'closePK']);
                Route::post('show-pk', [PkController::class, 'showPK']);
                Route::post('hide-pk', [PkController::class, 'hidePk']);

                // Microphone

                Route::post('liveTime', [MicrophoneController::class, 'lifeTime']);
                Route::post('up-microphone', [MicrophoneController::class, 'upMicrophone']);
                Route::post('leave-microphone', [MicrophoneController::class, 'goMicrophone']);
                Route::post('mute_microphone', [MicrophoneController::class, 'mute_microphone']);
                Route::post('unmute_microphone', [MicrophoneController::class, 'unmute_microphone']);
                Route::post('lock_microphone_place', [MicrophoneController::class, 'shut_microphone']);
                Route::post('unlock_microphone_place', [MicrophoneController::class, 'open_microphone']);
            });

            Route::get('/room-countries', [\App\Http\Controllers\Api\V1\RoomController::class, 'room_countries']);
            // end rooms api


            Route::prefix('account')->group(function () {
                Route::post('bind', [UserController::class, 'joinAccount']);

            });

            Route::prefix('search')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\v1\CommunityController::class, 'merge_search']);
                Route::get('user-friends', [\App\Http\Controllers\Api\v1\CommunityController::class, 'user_friends']);
                Route::get('/history', [\App\Http\Controllers\Api\v1\CommunityController::class, 'searchList']);
                Route::get('/clean_search_history', [\App\Http\Controllers\Api\v1\CommunityController::class, 'cleanSearchList']);
            });

            Route::prefix('merge_search')->group(function () {
                Route::post('/', [\App\Http\Controllers\Api\v1\CommunityController::class, 'merge_search']);
            });

            Route::prefix('community')->group(function () {
                Route::get('official_messages', [CommunityController::class, 'officialMessages']);
            });


            Route::prefix('families')->group(function () {
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

            Route::post('charge_to', [ChargeController::class, 'chargeTo']);
            Route::post('charge_history', [ChargeController::class, 'chargeHistory']);
            Route::prefix('agencies')->group(function () {
                Route::post('charge_co_for_users', [ChargeController::class, 'sendMoneyFoeHost']);
                Route::get('charge_co_for_usersHistory', [ChargeController::class, 'chargeCoForUsersHistory']);
                Route::post('charge_dollar_for_owner', [ChargeController::class, 'ChargeDollarForOwner']);
                Route::get('charge_dollar_for_OwnerHistory', [ChargeController::class, 'chargeDollarHistory']);
            });


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
            Route::get ('my-data',[\App\Http\Controllers\Api\V1\UserController::class,'my_data']);

            Route::prefix('profile')->group(function () {
                Route::get('get/{id}', [ProfileController::class, 'show']);
                Route::post('update', [ProfileController::class, 'update']);
                Route::get('visitors', [ProfileController::class, 'myProfileVisitorsList']);
                Route::post('liked', [ProfileController::class, 'liked']);
                Route::post('ignored', [ProfileController::class, 'ignored']);
                Route::get('users', [ProfileController::class, 'users']);
            });

            Route::prefix('relations')->group(function () {
                Route::get('/', [UserController::class, 'userFriend']);
                Route::post('follow', [\App\Http\Controllers\Api\V1\FollowController::class, 'follow']);
                Route::post('un-follow', [\App\Http\Controllers\Api\V1\FollowController::class, 'unFollow']);
                Route::post('is_user_friend', [\App\Http\Controllers\Api\V1\HomeController::class, 'check_if_friend']);
                Route::post('report_user', [\App\Http\Controllers\Api\V1\Report_userController::class, 'ReportUser']);
            });
            // end user api

            //start rankin
            Route::prefix('ranking')->group(function () {
                Route::post('/', [RankingController::class, 'ranking']);
                Route::post('/room', [\App\Http\Controllers\Api\V1\UserController::class, 'ranking_room']);
                Route::get('/top_user_ranking', [UserController::class, 'topUserRanking']);
            });
            // end ranking

            // start vips
            Route::prefix('vips')->group(function () {
                Route::get('/list', [VipController::class, 'vipList']);
                Route::post('/buyVip', [VipController::class, 'buyVip']);
                Route::post('/use', [VipController::class, 'vip_use']);
                Route::post('/send-to-user', [VipController::class, 'vip_send']);
            });
            Route::get('levels', [VipController::class, 'index']);
            // end vips

            // start levels

            Route::get ('levels-ranges',[UpgradeLevelController::class,'getLevelsRange']);

            // end levels
        }
    );

});
