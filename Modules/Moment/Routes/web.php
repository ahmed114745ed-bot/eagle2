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

Route::prefix('moment')->middleware(['appFeatureEnable:moment'])->group(function() {
    Route::get('/', 'MomentController@index');
});

Route::get('delete-moment/{moment_id}/{id}', 'MomentController@destroy_dash')->name('delete-moment')->middleware(['appFeatureEnable:moment']);

