<?php

use Illuminate\Support\Facades\Route;
use Utd\SpecialId\Http\Controllers\Dashboard\AdminSpecialIdController;

Route::controller(AdminSpecialIdController::class)->group(function () {
    Route::resource('admin-special-id', AdminSpecialIdController::class);
    Route::get('/Sort-special-id', 'sort');
    Route::post('/Change-Sort-special-id', 'change_sort');
    Route::post('/Send-special-id', 'send');
    Route::get('/enable-special-id/{ware_id}/{status}', 'enable_special_id');

    Route::get('/special-id-history', 'special_id_history');
    Route::get('/Sort-special-id', 'sort');
});
