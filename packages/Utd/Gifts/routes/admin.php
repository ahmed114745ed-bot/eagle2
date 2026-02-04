<?php

use Illuminate\Support\Facades\Route;
use Utd\Gifts\Http\Controllers\Admin\GiftController;
use Utd\Gifts\Http\Controllers\Admin\GiftCategoryController;
use Utd\Gifts\Http\Controllers\Admin\GiftLogController;

/*
|--------------------------------------------------------------------------
| Gifts Package Admin Routes
|--------------------------------------------------------------------------
|
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
            'gifts.installed', // Check if package is installed
        ],
        'as' => config('admin.route.prefix') . '.',
    ],
    function () {
        
        // Gifts Resource Routes
        Route::resource('gifts', GiftController::class, [
            'names' => [
                'index' => 'gifts'
            ]
        ]);
        
        // Lucky Gift Settings
        Route::get('lucky-gift-settings', [GiftController::class, 'luckyGiftSettings'])
            ->name('lucky-gift-settings');
        
        // Gift Categories Resource Routes
        Route::resource('gift-categories', GiftCategoryController::class);
        
        // Gift Categories Cache Clear (for Octane compatibility)
        Route::post('gift-categories/clear-cache', [GiftCategoryController::class, 'clearCache'])
            ->middleware(\App\Http\Middleware\DisableOctaneCaching::class)
            ->name('gift-categories.clear-cache');
        
        // Gift Categories Sortable Route (for Octane compatibility)
        Route::post('gift-categories/sort-update', [GiftCategoryController::class, 'sortUpdate'])
            ->middleware(\App\Http\Middleware\DisableOctaneCaching::class)
            ->name('gift-categories.sort-update');
        
        // Gift Logs Resource Routes
        Route::resource('gift-logs', GiftLogController::class)
            ->only(['index', 'show']);
        
        // Gift Statistics & Reports
        Route::prefix('gift-logs')->group(function () {
            Route::get('/statistics', [GiftLogController::class, 'statistics'])
                ->name('gift-logs.statistics');
            Route::get('/export', [GiftLogController::class, 'export'])
                ->name('gift-logs.export');
        });
    }
);
