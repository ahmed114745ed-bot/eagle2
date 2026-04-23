<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Utd\Form\Http\Controllers\CustomWidgetController;
use Utd\Form\Http\Controllers\Api\DataSourceController;
use Utd\Form\Http\Controllers\FormSubmissionController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('form-list', [DataSourceController::class, 'formList']);
});

Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('form-submissions', [FormSubmissionController::class, 'store']);
    });
});

Route::prefix('widgets')->name('widgets.')->group(function () {
    Route::get('/', [CustomWidgetController::class, 'index'])->name('index');
    Route::get('/{widget}/data', [CustomWidgetController::class, 'getData'])->name('data');
    Route::get('/bd-users', [CustomWidgetController::class, 'getBDUsers'])->name('bd-users');
    Route::get('/agencies', [CustomWidgetController::class, 'getAgencies'])->name('agencies');
    Route::get('/users', [CustomWidgetController::class, 'getUsers'])->name('users');
});

Route::prefix('data-sources')->name('data-sources.')->group(function () {
    Route::get('/{source}', [DataSourceController::class, 'getData'])->name('get');
});
