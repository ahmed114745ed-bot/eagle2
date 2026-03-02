<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the BadgeServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


use Illuminate\Support\Facades\Route;
use Utd\Badge\Http\Controllers\BadgeController;

Route::middleware(['auth:sanctum' ,'update.last.seen'])->group(function () {
    

    
    Route::get('/badges/users/{id}', [BadgeController::class,'index']);

});
