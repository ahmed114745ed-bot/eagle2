<?php

use Illuminate\Support\Facades\Route;
use Utd\SpecialId\Http\Controllers\Dashboard\AdminSpecialIdController;

Route::controller(AdminSpecialIdController::class)->group(function(){
    Route::resource('admin-special-id', AdminSpecialIdController::class);
    route::get('/Sort-special-id','sort');
    route::post('/Change-Sort-special-id','change_sort');
    route::post('/Send-special-id','send');
    route::get('/enable-special-id/{ware_id}/{status}','enable_special_id');

    route::get('/special-id-history','special_id_history');
    route::get('/Sort-special-id','sort');
});
