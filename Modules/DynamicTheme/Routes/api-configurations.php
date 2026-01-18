<?php

use Illuminate\Support\Facades\Route;
use Modules\DynamicTheme\Http\Controllers\ConfigurationController;
use Modules\DynamicTheme\Http\Controllers\ConfigurationOverrideController;
use Modules\DynamicTheme\Http\Controllers\Api\WidgetCustomizerController;
use Modules\DynamicTheme\Http\Controllers\Api\DesignTemplateController;
use Modules\DynamicTheme\Http\Controllers\Api\ChildCustomizerController;
use Modules\DynamicTheme\Http\Controllers\Api\UnifiedCustomizerEndpointController;

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

    // ======================== WIDGET CUSTOMIZER ROUTES ========================
    
    
    Route::prefix('customizers')->controller(WidgetCustomizerController::class)->group(function () {
        // Get all customizers for a widget override
        Route::get('widget-override/{configWidgetOverrideId}', 'indexByWidgetOverride')
            ->name('customizers.index-by-widget');
        
        // CRUD operations
        Route::get('{id}', 'show')->name('customizers.show');
        Route::post('/', 'store')->name('customizers.store');
        Route::put('{id}', 'update')->name('customizers.update');
        Route::delete('{id}', 'destroy')->name('customizers.destroy');
        
        // Generate CSS
        Route::get('{id}/css', 'generateCSS')->name('customizers.css');
        
        // Clone customizer
        Route::post('{id}/clone', 'clone')->name('customizers.clone');
    });

    // ======================== COLOR PRESET ROUTES ========================
    
    
    Route::prefix('color-presets')->controller(ColorPresetController::class)->group(function () {
        // Get all presets for a configuration
        Route::get('configuration/{configurationId}', 'indexByConfiguration')
            ->name('color-presets.index-by-config');
        
        // CRUD operations
        Route::get('{id}', 'show')->name('color-presets.show');
        Route::post('/', 'store')->name('color-presets.store');
        Route::put('{id}', 'update')->name('color-presets.update');
        Route::delete('{id}', 'destroy')->name('color-presets.destroy');
        
        // Get default preset
        Route::get('configuration/{configurationId}/default', 'getDefault')
            ->name('color-presets.default');
        
        // Export/Import
        Route::get('configuration/{configurationId}/export', 'export')
            ->name('color-presets.export');
        Route::post('import', 'import')->name('color-presets.import');
    });

    // ======================== DESIGN TEMPLATE ROUTES ========================
    
    
    Route::prefix('design-templates')->controller(DesignTemplateController::class)->group(function () {
        // Get all templates for a configuration
        Route::get('configuration/{configurationId}', 'indexByConfiguration')
            ->name('design-templates.index-by-config');
        
        // Get public templates
        Route::get('public/list', 'getPublic')->name('design-templates.public');
        
        // CRUD operations
        Route::get('{id}', 'show')->name('design-templates.show');
        Route::post('/', 'store')->name('design-templates.store');
        Route::put('{id}', 'update')->name('design-templates.update');
        Route::delete('{id}', 'destroy')->name('design-templates.destroy');
        
        // Export/Import
        Route::get('{id}/export', 'export')->name('design-templates.export');
        Route::post('import', 'import')->name('design-templates.import');
        
        // Duplicate template
        Route::post('{id}/duplicate', 'duplicate')->name('design-templates.duplicate');
    });

    // ======================== CHILD CUSTOMIZER ROUTES ========================
    
    
    Route::prefix('child-customizers')->controller(ChildCustomizerController::class)->group(function () {
        // Get all children for a widget override
        Route::get('widget-override/{configWidgetOverrideId}', 'indexByWidgetOverride')
            ->name('child-customizers.index-by-widget');
        
        // CRUD operations
        Route::get('{id}', 'show')->name('child-customizers.show');
        Route::post('/', 'store')->name('child-customizers.store');
        Route::put('{id}', 'update')->name('child-customizers.update');
        Route::delete('{id}', 'destroy')->name('child-customizers.destroy');
        
        // Generate CSS
        Route::get('{id}/generate-css', 'generateCSS')->name('child-customizers.generate-css');
        
        // Batch update
        Route::post('batch-update', 'batchUpdate')->name('child-customizers.batch-update');
        
        // Drawing endpoints
        Route::post('{id}/drawing', 'saveDrawing')->name('child-customizers.save-drawing');
        Route::get('{id}/drawing', 'getDrawing')->name('child-customizers.get-drawing');
        Route::get('config/{configId}/drawings', 'getConfigurationDrawings')->name('child-customizers.config-drawings');
        Route::get('{id}/export-drawing', 'exportWithDrawing')->name('child-customizers.export-drawing');
        Route::get('config/children', 'getConfigChildren')->name('child-customizers.config-children');
    });

    // ======================== UNIFIED CUSTOMIZER ENDPOINT (MAIN) ========================
    
    
    Route::prefix('configurations/{configId}/widgets/{widgetOverrideId}')->controller(UnifiedCustomizerEndpointController::class)->group(function () {
        // Get complete customization (widget + children + presets + templates + CSS)
        Route::get('/complete', 'getComplete')->name('customizers.complete');
        
        // Save complete configuration at once
        Route::post('/complete', 'saveComplete')->name('customizers.complete.save');
        
        // Export complete configuration
        Route::get('/complete/export', 'exportComplete')->name('customizers.complete.export');
        
        // Import complete configuration
        Route::post('/complete/import', 'importComplete')->name('customizers.complete.import');
        
        // Clone complete configuration to another widget
        Route::post('/complete/clone', 'cloneComplete')->name('customizers.complete.clone');
    });
});;
