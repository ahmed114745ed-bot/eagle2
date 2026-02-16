<?php

use Illuminate\Support\Facades\Route;
use Utd\Family\Http\Controllers\Admin\FamilyController;
use Utd\Family\Http\Controllers\Admin\FamilyLevelController;
use Utd\Family\Http\Controllers\Admin\FamilyConfigSettingController;
use Utd\Family\Http\Controllers\Admin\UserFamilyController;

/*
|--------------------------------------------------------------------------
| Family Package Admin Routes
|--------------------------------------------------------------------------
|
*/

Route::group(
    [
        'prefix' => config('admin.route.prefix'),
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            'multiLanguage',
        ],
    ],
    function () {
        Route::resource('families', FamilyController::class);
        Route::resource('family-users', UserFamilyController::class);
        Route::resource('family_levels', FamilyLevelController::class);
        Route::get('/setting-family', [FamilyConfigSettingController::class, 'index']);
        
        Route::get('/family-level', [FamilyController::class, 'familyLevelExcel'])->name('family-level');
        Route::get('/families-excel', [FamilyController::class, 'familiesExcel'])->name('families-excel');
    }
);
