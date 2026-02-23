<?php

use Illuminate\Support\Facades\Route;
use Utd\Vip\Http\Controllers\Dashboard\AdminPrivilegeVipsController;
use Utd\Vip\Http\Controllers\Dashboard\AdminVipsController;

// Vips Previlage
Route::resource('admin-vip-privilege', AdminPrivilegeVipsController::class);

// Vips
Route::controller(AdminVipsController::class)->group(function () {
    Route::resource('admin-vips', AdminVipsController::class);
    Route::get('/Sort-vips', 'sort');
    Route::post('/Send-vips', 'Send');
    Route::post('/Change-Sort-vips', 'change_sort');
    Route::get('/vips-autocomplete', 'autocomplete');
});
