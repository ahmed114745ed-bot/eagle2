<?php


use App\Helpers\Common;
use App\Http\Controllers\utd\MangerTypesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\utd\ReelsController;
use App\Http\Controllers\addTOjesonController;
use App\Http\Controllers\Api\V1\VipController;
use App\Http\Controllers\utd\FamilyController;
use App\Http\Controllers\utd\SilverController;
use App\Http\Controllers\Api\V1\CoinController;
use App\Http\Controllers\Api\V1\GiftController;
use App\Http\Controllers\Api\V1\OvipController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WareController;
use App\Http\Controllers\utd\MomentsController;
use App\Http\Controllers\utd\ExchangeController;
use App\Http\Controllers\utd\InterestController;
use App\Http\Controllers\utd\RoomVipsController;
use App\Admin\Controllers\AllStatisticController;
use App\Http\Controllers\Api\V1\ConfigController;
use App\Http\Controllers\Api\V1\TargetController;
use App\Http\Controllers\utd\GroupChatController;
use App\Http\Controllers\Api\V1\AllGameController;
use App\Http\Controllers\Api\V1\UtdUserController;
use App\Http\Controllers\utd\BackgroundController;
use App\Http\Controllers\utd\ImageColorController;
use App\Http\Controllers\utd\RoomTargetController;
use App\Http\Controllers\AddTargetToJsonController;
use App\Http\Controllers\utd\FamilyLevelController;
use App\Http\Controllers\utd\ParentUsersController;
use App\Http\Controllers\Api\V1\AdminUsersController;
use App\Http\Controllers\Api\V1\GameReportController;
use App\Http\Controllers\Api\V1\MangerTypeController;
use App\Http\Controllers\Api\V1\CoreWalletsController;
use App\Http\Controllers\Api\V1\TrashedUserController;
use App\Http\Controllers\utd\LevelIntervalsController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\AgencyStatisticController;
use App\Http\Controllers\utd\RewardLevelIntervalController;
use Modules\Public\Http\Controllers\web\LevelIntervalController;
use Modules\Achievement\Http\Controllers\UtdAchievementController;

// 'utd.decreptHeader'
// utd apis
Route::middleware([])->group(function () {
    //configs
    Route::prefix('configs')->group(function () {
        Route::get('/all', [ConfigController::class, 'index']);
        Route::post('/update', [ConfigController::class, 'updateConfig']);
        Route::get('/category', [ConfigController::class, "config"]);
    });

    Route::prefix('families')->group(function () {
        Route::get('/', [FamilyController::class, 'index']);
        Route::post('/', [FamilyController::class, 'store']);
        Route::post('/update/{id}', [FamilyController::class, 'update']);
        Route::post('/delete/{id}', [FamilyController::class, 'destroy']);
        Route::get('/{id}', [FamilyController::class, 'show']);
    });

    Route::prefix('level-intervals')->group(function () {
        Route::get('/', [LevelIntervalsController::class, 'index']);
        Route::post('/', [LevelIntervalsController::class, 'store']);
        Route::post('/update/{id}', [LevelIntervalsController::class, 'update']);
        Route::post('/delete/{id}', [LevelIntervalsController::class, 'destroy']);
        Route::get('/{id}', [LevelIntervalsController::class, 'show']);
    });

    Route::prefix('reward-level-interval/{reward_level_interval}')->group(function () {
        Route::get('/', [RewardLevelIntervalController::class, 'index']);
        Route::post('/', [RewardLevelIntervalController::class, 'store']);
        Route::post('/update/{id}', [RewardLevelIntervalController::class, 'update']);
        Route::post('/delete/{id}', [RewardLevelIntervalController::class, 'destroy']);

        Route::get('/{id}', [RewardLevelIntervalController::class, 'show']);
    });

    Route::prefix('group-chat')->group(function () {
        Route::get('/', [GroupChatController::class, 'index']);
        Route::post('/', [GroupChatController::class, 'store']);
        Route::post('/add-experience-points', [GroupChatController::class, 'add_experience_points']);
        Route::post('/update/{id}', [GroupChatController::class, 'update']);
        Route::post('/delete/{id}', [GroupChatController::class, 'destroy']);
        Route::get('/{id}', [GroupChatController::class, 'show']);
    });

    Route::prefix('image-colors')->group(function () {
        Route::get('/', [ImageColorController::class, 'index']);
        Route::post('/', [ImageColorController::class, 'store']);
        Route::post('/update/{id}', [ImageColorController::class, 'update']);
        Route::post('/delete/{id}', [ImageColorController::class, 'destroy']);
        Route::get('/{id}', [ImageColorController::class, 'show']);
    });

    Route::prefix('room-target')->group(function () {
        Route::get('/', [RoomTargetController::class, 'index']);
        Route::post('/', [RoomTargetController::class, 'store']);
        Route::post('/update/{id}', [RoomTargetController::class, 'update']);
        Route::post('/delete/{id}', [RoomTargetController::class, 'destroy']);
        Route::get('/{id}', [RoomTargetController::class, 'show']);
    });


    Route::prefix('backgrounds')->group(function () {
        Route::get('/', [BackgroundController::class, 'index']);
        Route::post('/', [BackgroundController::class, 'store']);
        Route::post('/update/{id}', [BackgroundController::class, 'update']);
        Route::post('/delete/{id}', [BackgroundController::class, 'destroy']);
        Route::get('/{id}', [BackgroundController::class, 'show']);
    });

    Route::prefix('parent-users')->group(function () {
        Route::get('/', [ParentUsersController::class, 'index']);
        Route::get('/{id}', [ParentUsersController::class, 'users']);
    });

    //games
    Route::prefix('games')->group(function () {
        Route::get('/all', [AllGameController::class, 'utdGameIndex']);
        Route::post('/create', [AllGameController::class, 'utdGameCreate']);
        Route::post('/update', [AllGameController::class, 'utdGameUpdate']);
        Route::post('/show', [AllGameController::class, 'showGame']);
        Route::post('/update-switch', [AllGameController::class, 'utdGameSwitchUpdate']);
        Route::get('/game-charge-details', [AllGameController::class, 'gameChargeDetails']);
    });
    // target
    Route::prefix('targets')->group(function () {
        Route::get('/all', [TargetController::class, 'index']);
        Route::post('/create', [TargetController::class, 'store']);
        Route::post('/update', [TargetController::class, 'update']);
        Route::post('/show', [TargetController::class, 'show']);
    });
    //ovip
    Route::prefix('ovips')->group(function () {
        Route::get('/all', [OvipController::class, 'index']);
        Route::post('/create', [OvipController::class, 'store']);
        Route::post('/update', [OvipController::class, 'update']);
        Route::post('/show', [OvipController::class, 'show']);
        Route::post('/ware-vip', [VipController::class, 'createWareVip']);
        Route::post('/show-privilege', [OvipController::class, 'showWithAllPrivileges']);
        Route::get('/ware-vips', [VipController::class, 'getWareVip']);
        Route::post('/delete-ware', [VipController::class, 'deleteWare']);
    });
    Route::get('all-vip-privileges', [OvipController::class, 'allVIP']);

    // agency statistic
    Route::get('agency-statistic', [AgencyStatisticController::class, 'statistic']);


    //admin users
    Route::prefix('admin_users')->group(function () {
        Route::get('/all', [AdminUsersController::class, 'index']);
        Route::post('/create', [AdminUsersController::class, 'store']);
        Route::get('/show/{id}', [AdminUsersController::class, 'show']);
        Route::get('/agency-user/{agencyId}', [AdminUsersController::class, 'showUserAgency']);
    });
    Route::prefix('gifts')->group(function () {
        Route::get('/all', [GiftController::class, 'allGifts']);
        Route::get('/type', [GiftController::class, 'typeGift']);
        Route::post('/create', [GiftController::class, 'store']);
        Route::post('/create-list', [GiftController::class, 'storeList']);
        Route::post('/update', [GiftController::class, 'update']);
        Route::post('/show', [GiftController::class, 'show']);
        Route::post('/update-music-switch', [GiftController::class, 'musicSwitchUpdate']);
        Route::post('/update-enable-switch', [GiftController::class, 'enableSwitchUpdate']);
        Route::post('/update-play-switch', [GiftController::class, 'isPlaySwitchUpdate']);
    });

    //ware
    Route::prefix('wares')->group(function () {
        Route::get('/all', [WareController::class, 'index']);
        Route::post('/create', [WareController::class, 'store']);
        Route::post('/create-list', [WareController::class, 'storeList']);
        Route::post('/update', [WareController::class, 'update']);
        Route::post('/show', [WareController::class, 'show']);
        Route::post('/update-enable-switch', [WareController::class, 'enableSwitchUpdate']);
        Route::get('/type', [WareController::class, 'typeWare']);
        Route::get('/get-type', [WareController::class, 'getTypeWare']);
    });
    Route::post('/create-paymentMethod', [PaymentMethodController::class, 'store']);


    // roles
    Route::resource('roles', RoleController::class);
    Route::get('permissions', [RoleController::class, "permissions"]);
    Route::get('permissions-category', [RoleController::class, "permissionsCategory"]);


    // users
    Route::resource('utd-users', UtdUserController::class);
    Route::get('utd-users/show/{id}', [UtdUserController::class, 'show']);



    Route::get('all-users', [UserController::class, 'userWithSearch']);

    //mangerType
    Route::prefix('manger-types')->group(function () {
        Route::get('/all', [MangerTypeController::class, 'index']);
        Route::post('/create', [MangerTypeController::class, 'store']);
        Route::post('/update', [MangerTypeController::class, 'update']);
        Route::post('/show', [MangerTypeController::class, 'show']);
    });
    //target Percentage
    Route::prefix('target-Percentage')->group(function () {
        Route::post('/create', [AddTargetToJsonController::class, 'create']);
        Route::get('/show', [AddTargetToJsonController::class, 'show']);
    });
    //setting config
    Route::prefix('setting-config')->group(function () {
        Route::post('/create', [addTOjesonController::class, 'create']);
        Route::get('/show', [addTOjesonController::class, 'show']);
    });
    //CoreWallets
    Route::prefix('core-wallets')->group(function () {
        Route::get('/all', [CoreWalletsController::class, 'index']);
        Route::post('/create', [CoreWalletsController::class, 'store']);
        Route::post('/update', [CoreWalletsController::class, 'update']);
        Route::get('/show/{id}', [CoreWalletsController::class, 'show']);
        Route::delete('delete/{id}', [CoreWalletsController::class, 'delete']);
    });

    //coin
    Route::prefix('coins')->group(function () {
        Route::get('/all/{payment_id}', [CoinController::class, 'index']);
        Route::post('/create/{payment_id}', [CoinController::class, 'store']);
        Route::post('/update', [CoinController::class, 'update']);
        Route::get('/show', [CoinController::class, 'show']);
        Route::get('/payment-gateway', [CoinController::class, 'paymentCoin']);
        Route::post('/payment-gateway/create', [CoinController::class, 'createPaymentGateway']);
        Route::post('/payment-gateway/update', [CoinController::class, 'updatePaymentGateway']);
        Route::post('/payment-gateway/show', [CoinController::class, 'showPaymentCoin']);
    });
    Route::get('/app-information', [AllStatisticController::class, 'appInformation']);


    Route::post('roles/preview',  [RoleController::class, 'preview']);


    // game report
    Route::get('/all-players', [GameReportController::class, 'allPlayers']);
    Route::get('/player-details/{id}', [GameReportController::class, 'playerDetails']);
    Route::post('/game-ranking/{id}', [GameReportController::class, 'gameRanking']);
    Route::get('/games-info/{id}', [GameReportController::class, 'gameInfo']);
    Route::get('/user-game-play/{id}', [GameReportController::class, 'gamePlay']);
    // end game report

    Route::prefix('trashed-account')->group(function () {
        Route::get('/', [TrashedUserController::class, 'trashedAccount']);
        Route::post('/restore/{id}', [TrashedUserController::class, 'restore']);
        Route::post('/delete/{id}', [TrashedUserController::class, 'softDelete']);
    });

    Route::prefix('users-level')->group(function () {
        Route::get('/', [UserController::class, 'userLevel']);
        Route::post('/update/{id}', [UserController::class, 'updateUserLevel']);
    });

    Route::prefix('device-token')->group(function () {
        Route::get('/', [UserController::class, 'usersDeviceToken']);
        Route::delete('/delete/{id}', [UserController::class, 'deleteDeviceToken']);
    });

    Route::prefix('family-levels')->group(function () {
        Route::get('/all', [FamilyLevelController::class, 'index']);
        Route::post('/show/{id}', [FamilyLevelController::class, 'show']);
        Route::post('/create', [FamilyLevelController::class, 'store']);
        Route::post('/update/{id}', [FamilyLevelController::class, 'update']);
        Route::post('/delete/{id}', [FamilyLevelController::class, 'destroy']);
    });

    Route::get('user-target', [UserController::class, 'usersTarget']);

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'allUsers']);
        Route::post('kick-agency/{id}', [UserController::class, 'kickAgency']);
        Route::post('kick-family/{id}', [UserController::class, 'kickFamily']);
        Route::post('change-agency', [UserController::class, 'changeAgency']);
        Route::post('update-switch', [UserController::class, 'updateSwitch']);
        Route::post('update-user-Setting', [UserController::class, 'updateUserSetting']);
        Route::post('create', [UserController::class, 'create']);
        Route::get('show/{id}', [UserController::class, 'showDataUser']);
        Route::post('update/{id}', [UserController::class, 'updateDataUser']);
    });




    Route::prefix('reels')->group(function () {
        Route::get('/all', [ReelsController::class, 'index']);
        Route::post('/show/{id}', [ReelsController::class, 'show']);
        Route::post('/search/{id}', [ReelsController::class, 'search']);
        Route::post('/delete/{id}', [ReelsController::class, 'destroy']);
        Route::post('/reelConfig/{id}', [ReelsController::class, 'reelConfig']);
    });
    Route::prefix('room-vips')->group(function () {
        Route::get('/all', [RoomVipsController::class, 'index']);
        Route::post('/show/{id}', [RoomVipsController::class, 'show']);
        Route::post('/search/{key}', [RoomVipsController::class, 'search']);
        Route::post('/delete/{id}', [RoomVipsController::class, 'destroy']);
        Route::post('create', [RoomVipsController::class, 'store']);
        Route::post('update/{id}', [RoomVipsController::class, 'update']);
    });
    Route::get('users-search', [UserController::class, 'search']);
    Route::get('users-search2', [UserController::class, 'search2']);
    Route::prefix('achievements')->group(function () {
        Route::get('/all', [UtdAchievementController::class, 'allAchievements']);
        Route::get('/{achievementId}/level', [UtdAchievementController::class, 'allAchievementsLevel']);
        Route::post('/create-level', [UtdAchievementController::class, 'createAchievementLevel']);
        Route::post('/update-level/{id}', [UtdAchievementController::class, 'updateAchievementLevel']);
        Route::get('/show/{id}', [UtdAchievementController::class, 'showAchievementLevel']);
        Route::get('/target-level', [UtdAchievementController::class, 'achievementTargetType']);
        Route::get('/{achievementId}/all-users-gift-achievement', [UtdAchievementController::class, 'allUsersGiftAchievements']);
        Route::post('/create-user-gift', [UtdAchievementController::class, 'createUserAchievementGift']);
        Route::get('/gift-achievement', [UtdAchievementController::class, 'giftAchievement']);
        Route::get('/user-achievement-level', [UtdAchievementController::class, 'allUserAchievementLevel']);
        Route::post('/enable/{id}', [UtdAchievementController::class, 'isEnable']);
        Route::post('/delete_user_level/{id}', [UtdAchievementController::class, 'deleteUserAchievementLevel']);
        Route::post('/create/user-level', [UtdAchievementController::class, 'createUserAchievementLevel']);
        Route::get('/gift-user-level', [UtdAchievementController::class, 'userAchievementLevelGiftIndex']);
        Route::get('/target-user-level/{achievementId}', [UtdAchievementController::class, 'getAchievementLevelsTarget']);
    });

    Route::prefix('interests')->group(function () {
        Route::get('/', [InterestController::class, 'all']);
        Route::get('/show/{id}', [InterestController::class, 'show']);
        Route::post('/create', [InterestController::class, 'create']);
        Route::post('/update/{id}', [InterestController::class, 'update']);
        Route::delete('/delete/{id}', [InterestController::class, 'destroy']);
    });

    Route::prefix('silvers')->group(function () {
        Route::get('/', [SilverController::class, 'all']);
        Route::get('/show/{id}', [SilverController::class, 'show']);
        Route::post('/create', [SilverController::class, 'create']);
        Route::post('/update/{id}', [SilverController::class, 'update']);
        Route::delete('/delete/{id}', [SilverController::class, 'destroy']);
    });

    Route::prefix('exchanges')->group(function () {
        Route::get('/', [ExchangeController::class, 'all']);
        Route::get('/show/{id}', [ExchangeController::class, 'show']);
        Route::post('/create', [ExchangeController::class, 'create']);
        Route::post('/update/{id}', [ExchangeController::class, 'update']);
        Route::delete('/delete/{id}', [ExchangeController::class, 'destroy']);
    });
    Route::get('codes', [UserController::class, 'allCodes']);

    Route::prefix('moment')->group(function () {
        Route::get('/', [MomentsController::class, 'all']);
        Route::get('/show/{id}', [MomentsController::class, 'show']);
        Route::post('/search/{id}', [MomentsController::class, 'search']);
        Route::delete('/delete/{id}', [MomentsController::class, 'destroy']);
        Route::post('/config/{id}', [MomentsController::class, 'config']);
    });
    Route::prefix('manger-types')->group(function () {
        Route::get('/', [MangerTypesController::class, 'all']);
        Route::get('/show/{id}', [MangerTypesController::class, 'show']);
        Route::post('/create', [MangerTypesController::class, 'create']);
        Route::post('/update/{id}', [MangerTypesController::class, 'update']);
        Route::delete('/delete/{id}', [MangerTypesController::class, 'destroy']);
    });
});
