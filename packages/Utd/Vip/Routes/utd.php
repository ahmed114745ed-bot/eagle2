<?php

use Illuminate\Support\Facades\Route;
use Utd\Vip\Http\Controllers\Api\DedicateVipController;
use Utd\Vip\Http\Controllers\Api\OvipController;
use Utd\Vip\Http\Controllers\Api\VipController;

Route::prefix('vips-dedicate')->group(function () {
    Route::get('/', [DedicateVipController::class, 'index']);
    Route::post('/delete-all', [DedicateVipController::class, 'delete_all']);
    Route::post('dedicate/{id}', [DedicateVipController::class, 'dedicate']);
});

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
