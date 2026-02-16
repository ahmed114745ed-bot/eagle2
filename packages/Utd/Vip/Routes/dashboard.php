<?php

use Illuminate\Support\Facades\Route;
use Utd\Vip\Http\Controllers\Dashboard\AdminPrivilegeVipsController;
use Utd\Vip\Http\Controllers\Dashboard\AdminVipsController;

//Vips Previlage
Route::resource('admin-vip-privilege', AdminPrivilegeVipsController::class);

//Vips
Route::controller(AdminVipsController::class)->group(function(){
    Route::resource('admin-vips', AdminVipsController::class);
    route::get('/Sort-vips','sort');
    route::post('/Send-vips','Send');
    route::post('/Change-Sort-vips','change_sort');
    route::get('/vips-autocomplete','autocomplete');
});
