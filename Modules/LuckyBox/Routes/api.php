<?php

use Illuminate\Http\Request;
use Modules\LuckyBox\Http\Controllers\BoxController;

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


Route::middleware(['auth:sanctum', 'checkLatestToken', 'generalBan', 'userBan'])->group(
    function () {
        Route::prefix('box')->group(function () {
            Route::get('list', [BoxController::class, 'index']);
            Route::post('send', [BoxController::class, 'send']);
            Route::post('send_test', [BoxController::class, 'testSendSuperBoxes']);
            Route::post('pickup', [BoxController::class, 'pickBox']);
        });
    }
);
