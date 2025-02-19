<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('reals')->middleware("appFeatureEnable:reel")->group(function() {
    Route::get('/', 'RealsController@index');
    Route::get('delete-reel/{real_id}/{id}', 'RealsController@destroy_dash')->name('delete-reel')->middleware(['appFeatureEnable:reel']);
});
