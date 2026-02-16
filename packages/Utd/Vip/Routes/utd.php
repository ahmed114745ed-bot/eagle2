<?php

use Illuminate\Support\Facades\Route;
use Utd\Vip\Http\Controllers\Api\DedicateVipController;

Route::prefix('vips-dedicate')->group(function () {
    Route::get('/', [DedicateVipController::class, 'index']);
    Route::post('/delete-all', [DedicateVipController::class, 'delete_all']);
    Route::post('dedicate/{id}', [DedicateVipController::class, 'dedicate']);
});
