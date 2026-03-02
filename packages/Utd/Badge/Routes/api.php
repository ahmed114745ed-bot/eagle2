<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Utd\Badge\Http\Controllers\BadgeController;

Route::middleware(['auth:sanctum' ,'update.last.seen'])->group(function () {
    Route::get('/badges/users/{id}', [BadgeController::class,'index']);
});
