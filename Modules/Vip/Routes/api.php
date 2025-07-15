<?php

use Illuminate\Http\Request;
use Modules\Vip\Http\Controllers\Api\VipController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/vip', function (Request $request) {
    // return $request->user();
    Route::prefix('vips')->middleware(['appFeatureEnable:vips'])->group(function () {
        Route::get('/list', [VipController::class, 'vipList']);
        Route::get('/user/list', [VipController::class, 'vipUserList']);
        Route::post('/buyVip', [VipController::class, 'buyVip']);
        Route::post('/buy-vip-percentage', [VipController::class, 'buyVipPercentage']);
        Route::post('/use', [VipController::class, 'vip_use']);
        Route::post('/use-pack', [VipController::class, 'pack_use']);
        Route::post('/send-to-user', [VipController::class, 'vip_send']);
    });
    Route::get('levels/badges', [VipController::class, 'badges']);
    Route::get('levels', [VipController::class, 'index']);

});