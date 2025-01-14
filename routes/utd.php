<?php


use App\Helpers\Common;
use App\Http\Controllers\utd\FamilyLevelController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\addTOjesonController;
use App\Http\Controllers\Api\V1\VipController;
use App\Http\Controllers\Api\V1\CoinController;
use App\Http\Controllers\Api\V1\GiftController;
use App\Http\Controllers\Api\V1\OvipController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WareController;
use App\Admin\Controllers\AllStatisticController;
use App\Http\Controllers\Api\V1\ConfigController;
use App\Http\Controllers\Api\V1\TargetController;
use App\Http\Controllers\Api\V1\AllGameController;
use App\Http\Controllers\Api\V1\UtdUserController;
use App\Http\Controllers\AddTargetToJsonController;
use App\Http\Controllers\Api\V1\AdminUsersController;
use App\Http\Controllers\Api\V1\GameReportController;
use App\Http\Controllers\Api\V1\MangerTypeController;
use App\Http\Controllers\Api\V1\CoreWalletsController;
use App\Http\Controllers\Api\V1\TrashedUserController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\AgencyStatisticController;
use App\Http\Controllers\utd\FamilyController;
use App\Http\Controllers\utd\LevelIntervalsController;
use App\Http\Controllers\utd\RewardLevelIntervalController;
use Modules\Public\Http\Controllers\web\LevelIntervalController;

// 'utd.decreptHeader'
// utd apis
Route::middleware([])->group(function () {
    //configs
    Route::prefix('configs')->group(function () {
        Route::get('/all', [ConfigController::class, 'index']);
        Route::post('/update', [ConfigController::class, 'updateConfig']);
        Route::get('/category', [ConfigController::class, "config"]);
    });

    Route::prefix('families')->group(function(){
        Route::get('/', [FamilyController::class, 'index']);
        Route::post('/', [FamilyController::class, 'store']);
        Route::post('/update/{id}', [FamilyController::class, 'update']);
        Route::post('/delete/{id}', [FamilyController::class, 'destroy']);
        Route::get('/{id}', [FamilyController::class, 'show']);
    });

    Route::prefix('level-intervals')->group(function(){
        Route::get('/', [LevelIntervalsController::class, 'index']);
        Route::post('/', [LevelIntervalsController::class, 'store']);
        Route::post('/update/{id}', [LevelIntervalsController::class, 'update']);
        Route::post('/delete/{id}', [LevelIntervalsController::class, 'destroy']);
        Route::get('/{id}', [LevelIntervalsController::class, 'show']);
    });

    Route::prefix('reward-level-interval/{reward_level_interval}')->group(function(){
        Route::get('/', [RewardLevelIntervalController::class, 'index']);
        Route::post('/', [RewardLevelIntervalController::class, 'store']);
        Route::post('/update/{id}', [RewardLevelIntervalController::class, 'update']);
        Route::post('/delete/{id}', [RewardLevelIntervalController::class, 'destroy']);

        Route::get('/{id}', [RewardLevelIntervalController::class, 'show']);

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
        Route::post('/show', [AdminUsersController::class, 'show']);
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
        Route::post('/show', [CoreWalletsController::class, 'show']);
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
        Route::post('/update', [FamilyLevelController::class, 'update']);
        Route::post('/delete', [FamilyLevelController::class, 'destroy']);


    });


});
