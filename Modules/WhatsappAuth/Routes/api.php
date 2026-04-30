<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\WhatsappAuth\Http\Controllers\WhatsappController;

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

Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.')->group(function () {
    Route::get('whatsappauth', fn (Request $request) => $request->user())->name('whatsappauth');
});

Route::get('verification_code', [\Modules\WhatsappAuth\Http\Controllers\WhatsappController::class, 'index'])->middleware(['localization']);
Route::prefix('auth')->group(function () {
    Route::post('register-whatsapp', [\Modules\WhatsappAuth\Http\Controllers\WhatsappController::class, 'registerWithWhatsapp'])->middleware('throttle:auth-register');
    Route::post('forget-password-whatsapp', [\Modules\WhatsappAuth\Http\Controllers\WhatsappController::class, 'resetWhatsapp'])->middleware('throttle:auth-otp');
    Route::post('whatsapp-webhook', [\Modules\WhatsappAuth\Http\Controllers\WhatsappController::class, 'whatsappWebhook']);
});
//Route::post('send-code-whatsapp',[WhatsappController::class, 'sendCodeWhatsapp']);
Route::post ('send-code-whatsapp',[\App\Http\Controllers\Api\V2\Auth\RegisterController::class,'sendWhatsAapOtp'])->middleware('throttle:auth-otp');
Route::prefix('account')->middleware(['auth:sanctum', 'localization'])->group(function () {
    Route::post('change-phone-whatsapp', [\Modules\WhatsappAuth\Http\Controllers\WhatsappController::class, 'changePhoneWhatsapp']);
    Route::post('reset-password-whatsapp', [\Modules\WhatsappAuth\Http\Controllers\WhatsappController::class, 'resetWhatsappN']);
});
