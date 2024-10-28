<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\addTOjesonController;
use App\Http\Controllers\Api\V1\VipController;
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
use App\Http\Controllers\Api\V1\MangerTypeController;
use App\Http\Controllers\Api\V1\CoreWalletsController;
use App\Http\Controllers\Api\V1\AgencyStatisticController;


// utd apis
Route::middleware([])->group(function () {
    //configs
    Route::prefix('configs')->group(function () {
        Route::get('/all', [ConfigController::class, 'index']);
        Route::post('/update', [ConfigController::class, 'updateConfig'])->middleware('decrypt.data');
    });
    //games
    Route::prefix('games')->group(function () {
        Route::get('/all', [AllGameController::class, 'utdGameIndex']);
        Route::post('/create', [AllGameController::class, 'utdGameCreate']);
        Route::post('/update', [AllGameController::class, 'utdGameUpdate']);
        Route::post('/show', [AllGameController::class, 'showGame']);
        Route::post('/update-switch', [AllGameController::class, 'utdGameSwitchUpdate']);
    });
    // target
    Route::prefix('targets')->group(function () {
        Route::get('/all', [TargetController::class, 'index']);
        Route::post('/create', [TargetController::class, 'store'])->middleware('decrypt.data');
        Route::post('/update', [TargetController::class, 'update'])->middleware('decrypt.data');
        Route::post('/show', [TargetController::class, 'show']);
    });
    //ovip
    Route::prefix('ovips')->group(function () {
        Route::get('/all', [OvipController::class, 'index']);
        Route::post('/create', [OvipController::class, 'store'])->middleware('decrypt.data');
        Route::post('/update', [OvipController::class, 'update'])->middleware('decrypt.data');
        Route::post('/show', [OvipController::class, 'show']);
        Route::post('/ware-vip', [VipController::class, 'createWareVip'])->middleware('decrypt.data');
        Route::post('/show-privilege', [OvipController::class, 'showWithAllPrivileges']);
        Route::get('/ware-vips', [VipController::class, 'getWareVip']);
        Route::post('/delete-ware', [VipController::class, 'deleteWare'])->middleware('decrypt.data');


    });
    Route::get('all-vip-privileges', [OvipController::class, 'allVIP']);

    // agency statistic
    Route::get('agency-statistic', [AgencyStatisticController::class, 'statistic'])->middleware('decrypt.data');


    //admin users
    Route::prefix('admin_users')->group(function () {
        Route::get('/all', [AdminUsersController::class, 'index']);
        Route::post('/create', [AdminUsersController::class, 'store'])->middleware('decrypt.data');
        Route::post('/show', [AdminUsersController::class, 'show']);
    });
    Route::prefix('gifts')->group(function () {
        Route::get('/all', [GiftController::class, 'allGifts']);
        Route::get('/type', [GiftController::class, 'typeGift']);
        Route::post('/create', [GiftController::class, 'store']);
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
        Route::post('/update', [WareController::class, 'update']);
        Route::post('/show', [WareController::class, 'show']);
        Route::post('/update-enable-switch', [WareController::class, 'enableSwitchUpdate']);
        Route::get('/type', [WareController::class, 'typeWare']); 
        Route::get('/get-type', [WareController::class, 'getTypeWare']);
       

    });
    // roles
    Route::resource('roles', RoleController::class)->middleware('decrypt.data');
    Route::get('permissions', [RoleController::class, "permissions"])->middleware('decrypt.data');
    Route::get('permissions-category', [RoleController::class, "permissionsCategory"]);

    // users
    Route::resource('utd-users', UtdUserController::class)->middleware('decrypt.data');



    Route::get('all-users', [UserController::class, 'userWithSearch']);

    //mangerType
    Route::prefix('manger-types')->group(function () {
        Route::get('/all', [MangerTypeController::class, 'index']);
        Route::post('/create', [MangerTypeController::class, 'store'])->middleware('decrypt.data');
        Route::post('/update', [MangerTypeController::class, 'update'])->middleware('decrypt.data');
        Route::post('/show', [MangerTypeController::class, 'show']);
    });
    //target Percentage
    Route::prefix('target-Percentage')->group(function () {
        Route::post('/create', [AddTargetToJsonController::class, 'create'])->middleware('decrypt.data');
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
        Route::post('/create', [CoreWalletsController::class, 'store'])->middleware('decrypt.data');
        Route::post('/update', [CoreWalletsController::class, 'update'])->middleware('decrypt.data');
        Route::post('/show', [CoreWalletsController::class, 'show']);
    });
    Route::get('/app-information', [AllStatisticController::class, 'appInformation']);


    Route::post('roles/preview',  [RoleController::class, 'preview'])->middleware('decrypt.data');

});


