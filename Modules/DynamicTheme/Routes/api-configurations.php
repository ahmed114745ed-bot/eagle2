<?php

use Illuminate\Support\Facades\Route;
use Modules\DynamicTheme\Http\Controllers\ConfigurationController;
use Modules\DynamicTheme\Http\Controllers\ConfigurationOverrideController;

/**
 * Configuration Management Routes
 * 
 * Base URL: /api/configurations
 */

Route::middleware(['auth:sanctum'])->group(function () {
    
    // ======================== MAIN CONFIGURATION ROUTES ========================
    
    /**
     * Configuration CRUD operations
     */
    Route::controller(ConfigurationController::class)->group(function () {
        // Get all configurations
        Route::get('/', 'index')->name('configurations.index');
        
        // Create new configuration
        Route::post('/', 'store')->name('configurations.store');
        
        // Get single configuration
        Route::get('{id}', 'show')->name('configurations.show');
        
        // Update configuration
        Route::put('{id}', 'update')->name('configurations.update');
        
        // Delete configuration
        Route::delete('{id}', 'destroy')->name('configurations.destroy');
        
        // Clone configuration
        Route::post('{id}/clone', 'clone')->name('configurations.clone');
        
        // Activate configuration
        Route::post('{id}/activate', 'activate')->name('configurations.activate');
        
        // Get configuration statistics
        Route::get('{id}/stats', 'getStats')->name('configurations.stats');
        
        // Get configuration activity log
        Route::get('{id}/activity', 'getActivity')->name('configurations.activity');
        
        // Compare two configurations
        Route::post('/compare', 'compare')->name('configurations.compare');
    });

    // ======================== SCREEN OVERRIDE ROUTES ========================
    
    Route::controller(ConfigurationOverrideController::class)->group(function () {
        /**
         * Screen Overrides
         * Base: /api/configurations/{configId}/screen-overrides
         */
        Route::prefix('{configId}/screen-overrides')->group(function () {
            Route::get('/', 'getScreenOverrides')->name('screen-overrides.index');
            Route::post('/', 'saveScreenOverride')->name('screen-overrides.store');
            Route::delete('{screenId}', 'deleteScreenOverride')->name('screen-overrides.destroy');
        });

        /**
         * Widget Overrides
         * Base: /api/configurations/{configId}/widget-overrides
         */
        Route::prefix('{configId}/widget-overrides')->group(function () {
            Route::get('/', 'getWidgetOverrides')->name('widget-overrides.index');
            Route::post('/', 'saveWidgetOverride')->name('widget-overrides.store');
            Route::delete('{screenWidgetId}', 'deleteWidgetOverride')->name('widget-overrides.destroy');
        });

        /**
         * Theme Child Overrides
         * Base: /api/configurations/{configId}/theme-child-overrides
         */
        Route::prefix('{configId}/theme-child-overrides')->group(function () {
            Route::get('/', 'getThemeChildOverrides')->name('theme-child-overrides.index');
            Route::post('/', 'saveThemeChildOverride')->name('theme-child-overrides.store');
            Route::delete('{childOverrideId}', 'deleteThemeChildOverride')->name('theme-child-overrides.destroy');
        });

        /**
         * Asset Overrides
         * Base: /api/configurations/{configId}/asset-overrides
         */
        Route::prefix('{configId}/asset-overrides')->group(function () {
            Route::get('/', 'getAssetOverrides')->name('asset-overrides.index');
            Route::post('/', 'saveAssetOverride')->name('asset-overrides.store');
            Route::delete('{themeAssetId}', 'deleteAssetOverride')->name('asset-overrides.destroy');
        });

        /**
         * Child Asset Overrides
         * Base: /api/configurations/{configId}/child-asset-overrides
         */
        Route::prefix('{configId}/child-asset-overrides')->group(function () {
            Route::get('/', 'getChildAssetOverrides')->name('child-asset-overrides.index');
            Route::post('/', 'saveChildAssetOverride')->name('child-asset-overrides.store');
            Route::delete('{childAssetOverrideId}', 'deleteChildAssetOverride')->name('child-asset-overrides.destroy');
        });

        /**
         * Bulk Operations
         * Base: /api/configurations/{configId}/bulk
         */
        Route::prefix('{configId}/bulk')->group(function () {
            Route::post('update-visibility', 'bulkUpdateVisibility')->name('bulk.update-visibility');
            Route::post('reorder', 'bulkReorder')->name('bulk.reorder');
        });
    });
});
