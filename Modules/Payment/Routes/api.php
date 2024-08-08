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


Route::middleware('auth:sanctum')->group (
    function (){
        Route::get('initial', [\Modules\Payment\Http\Controllers\PaymentController::class, 'initial']);

        Route::get('payment-create', [\Modules\Payment\Http\Controllers\PaymentController::class, 'create']);
    }
);

Route::get('/payment-callback/{reference_id?}',[\Modules\Payment\Http\Controllers\PaymentController::class,'payment_verify'])->name('payment-verify');
