<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/whatsapp', function (Request $request) {
    return $request->user();
});

Route::prefix('')->middleware(['appFeatureEnable:whatsapp'])->group(function(){
    Route::post('whatsapp-webhook-received', [\Modules\Whatsapp\Http\Controllers\WhatsappController::class, 'webhook']);
    Route::get('whatsapp-webhook-received', [\Modules\Whatsapp\Http\Controllers\WhatsappController::class, 'getWebhook']);
    Route::get('verification_code', [\Modules\Whatsapp\Http\Controllers\ClientController::class, 'index'])->middleware(['localization']);
    Route::get('verification_code_service', [\Modules\Whatsapp\Http\Controllers\ClientController::class, 'service'])->middleware(['auth:sanctum', 'whatsapp','localization']);
    Route::post('server/auth/login', [\Modules\Whatsapp\Http\Controllers\ClientController::class, 'login']);
    Route::get('send-code-service', [\Modules\Whatsapp\Http\Controllers\OtpController::class, 'sendMessageClient'])->middleware(['auth:sanctum', 'whatsapp','localization']);
    Route::get('send-code-whatsapp', [\Modules\Whatsapp\Http\Controllers\OtpController::class, 'sendMessageClient'])->middleware(['auth:sanctum', 'whatsapp','localization']);
});
