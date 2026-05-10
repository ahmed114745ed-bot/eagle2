<?php

use Utd\Bd\Http\Controllers\Admin\BdController;
use Utd\Bd\Http\Controllers\Admin\BdSelectController;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => config('admin.route.prefix'),
        'namespace' => config('admin.route.namespace'),
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
        ],
        'as' => config('admin.route.prefix') . '.',
    ],
    function () {
        Route::resource('usersBd', BdController::class);
        Route::resource('user-Bds', BdController::class);
        Route::resource('usersBd-settings', BdSelectController::class);

        Route::post('toggle-salary-transfer', [BdSelectController::class, 'toggleSalaryTransfer'])->name('bd.toggle-salary-transfer');
        Route::post('userBd/make-default', [BdSelectController::class, 'makeDefault'])->name('make-bd-default');
        Route::get('userBd/select', [BdSelectController::class, 'index'])->name('userBd.select');

        Route::get('professional-bd', [BdController::class, 'professionalBd']);
    }
);
