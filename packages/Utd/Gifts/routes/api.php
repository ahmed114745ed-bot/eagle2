<?php

use Illuminate\Support\Facades\Route;



Route::prefix('api/')->middleware(['api', 'auth:sanctum'])->group(function () {
    
    // Gift Routes
    Route::prefix('gifts')->group(function () {
        Route::get('/', 'Utd\Gifts\Http\Controllers\Api\GiftController@index');
        Route::get('/v2', 'Utd\Gifts\Http\Controllers\Api\GiftController@getByCategory');
        Route::get('/images', 'Utd\Gifts\Http\Controllers\Api\GiftController@get_images');
        Route::post('/send', 'Utd\Gifts\Http\Controllers\Api\GiftLogController@gift_queue_cp');
        Route::post('/send2', 'Utd\Gifts\Http\Controllers\Api\GiftLogController@gift_queue_cp');
        Route::post('/send-lucky-gift-combo', 'Utd\Gifts\Http\Controllers\Api\GiftLogController@sendLuckyGift2')
            ->middleware(['checkCpu', 'appFeatureEnable:lucky']);
        Route::post('/v2/send-lucky-gift-combo', 'Utd\Gifts\Http\Controllers\Api\GiftLogController@sendLuckyGift2V2')
            ->middleware(['checkCpu', 'appFeatureEnable:lucky']);
    });

    // Gift Categories Routes
    Route::prefix('gift-categories')->group(function () {
        Route::get('/', 'Utd\Gifts\Http\Controllers\Api\GiftCategoryController@index');
    });

    // Gift Logs Routes
    Route::prefix('gift-logs')->group(function () {
        Route::get('/', 'Utd\Gifts\Http\Controllers\Api\GiftLogController@index');
        Route::get('/report', 'Utd\Gifts\Http\Controllers\Api\GiftLogController@report');
    });
});
