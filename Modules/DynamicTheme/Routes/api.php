<?php

use Modules\DynamicTheme\Http\Controllers\Api\Admin\LibraryAssetController;
use Modules\DynamicTheme\Http\Controllers\Api\V1\ScreenController;
use Modules\DynamicTheme\Http\Controllers\Api\V1\WidgetController;
use Modules\DynamicTheme\Http\Controllers\Api\V1\AssetController;
use Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ScreenManagementController;
use Modules\DynamicTheme\Http\Controllers\Api\Dashboard\WidgetManagementController;
use Modules\DynamicTheme\Http\Controllers\Api\Admin\AdminWidgetController;
use Modules\DynamicTheme\Http\Controllers\Api\Admin\AdminThemeController;
use Modules\DynamicTheme\Http\Controllers\Api\Admin\AdminScreenController;
use Modules\DynamicTheme\Http\Controllers\Api\Admin\AdminImportExportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ============================================================
// Mobile App API (v1) - For Flutter consumption
// ============================================================

Route::prefix('v1/app')->group(function () {
    Route::get('/screens/{screenKey}', [ScreenController::class, 'show']);
});



Route::prefix('v1')->group(function () {
    // Screens - Get screen layout with widgets for mobile app
    Route::get('/screens', [ScreenController::class, 'index']);
    Route::get('/screens/{screenKey}', [ScreenController::class, 'show']);

    // Widgets - Get available widgets and themes
    Route::get('/widgets', [WidgetController::class, 'index']);
    Route::get('/widgets/{widgetKey}', [WidgetController::class, 'show']);
    Route::get('/widgets/{widgetKey}/themes', [WidgetController::class, 'themes']);
    Route::get('/themes/{themeKey}', [WidgetController::class, 'showTheme']);
});

// ============================================================
// Dashboard API - For admin dashboard management
// ============================================================
Route::prefix('dashboard')->group(function () {

    Route::get('/library/assets', [LibraryAssetController::class, 'index']);
    Route::post('/library/assets', [LibraryAssetController::class, 'store']);
    // Screen Management
    Route::prefix('screens')->group(function () {
        Route::get('/', [ScreenManagementController::class, 'index']);
        Route::post('/', [ScreenManagementController::class, 'store']);
        Route::get('/{id}', [ScreenManagementController::class, 'show']);
        Route::put('/{id}', [ScreenManagementController::class, 'update']);
        Route::delete('/{id}', [ScreenManagementController::class, 'destroy']);
        Route::post('/{id}/duplicate', [ScreenManagementController::class, 'duplicate']);

        // Screen Widgets
        Route::post('/{screenId}/widgets', [ScreenManagementController::class, 'addWidget']);
        Route::put('/{screenId}/widgets', [ScreenManagementController::class, 'addWidget']);
        Route::put('/{screenId}/widgets/{widgetId}', [ScreenManagementController::class, 'updateWidget']);
        Route::delete('/{screenId}/widgets/{widgetId}', [ScreenManagementController::class, 'removeWidget']);
        Route::post('/{screenId}/widgets/reorder', [ScreenManagementController::class, 'reorderWidgets']);

        // Screen Widget Children
        Route::post('/{screenId}/widgets/{widgetId}/children', [ScreenManagementController::class, 'addWidgetChild']);
        Route::post('/widgets/{widgetId}/children', [ScreenManagementController::class, 'addWidgetChildV2']);
        Route::put('/{screenId}/widgets/{widgetId}/children/{childId}', [ScreenManagementController::class, 'updateWidgetChild']);
        Route::delete('/{screenId}/widgets/{widgetId}/children/{childId}', [ScreenManagementController::class, 'removeWidgetChild']);
    });

    // Widget Management
    Route::prefix('widgets')->group(function () {
        Route::get('/', [WidgetManagementController::class, 'index']);
        Route::post('/', [WidgetManagementController::class, 'store']);
        Route::get('/{id}', [WidgetManagementController::class, 'show']);
        Route::put('/{id}', [WidgetManagementController::class, 'update']);
        Route::delete('/{id}', [WidgetManagementController::class, 'destroy']);

        // Widget Themes
        Route::get('/{widgetId}/themes', [AdminThemeController::class, 'getByWidget']);
        Route::post('/{widgetId}/themes', [WidgetManagementController::class, 'addTheme']);
        Route::put('/{widgetId}/themes/{themeId}', [WidgetManagementController::class, 'updateTheme']);
        Route::delete('/{widgetId}/themes/{themeId}', [WidgetManagementController::class, 'deleteTheme']);

        // Widget Settings
        Route::post('/{widgetId}/settings', [WidgetManagementController::class, 'addSetting']);
        Route::put('/{widgetId}/settings/{settingId}', [WidgetManagementController::class, 'updateSetting']);
        Route::delete('/{widgetId}/settings/{settingId}', [WidgetManagementController::class, 'deleteSetting']);

        // Widget Actions
        Route::post('/{widgetId}/actions', [WidgetManagementController::class, 'addAction']);
        Route::delete('/{widgetId}/actions/{actionId}', [WidgetManagementController::class, 'deleteAction']);

    });

    Route::get('/widgets-parents', [WidgetController::class, 'parents']);

    // Visual Designer Position Updates
    Route::put('/theme-children/{id}/position', [AdminThemeController::class, 'updateChildPosition']);
    Route::put('/theme-assets/{id}/position', [AdminThemeController::class, 'updateAssetPosition']);
    Route::post('/designer/batch-update-positions', [AdminThemeController::class, 'batchUpdatePositions']);
    Route::put('/widgets/{id}/dimensions', [AdminThemeController::class, 'updateWidgetDimensions']);

     //children
    Route::post('/theme-children', [AdminThemeController::class, 'theme_children']);
    Route::put('/theme-children/{id}', [AdminThemeController::class, 'UpdateThemeChild']);
    Route::delete('/theme-children/{id}', [AdminThemeController::class, 'deleteThemeChild']);
    Route::delete('/theme-children/{id}/hide', [AdminThemeController::class, 'HideThemeChild']);

    //children assets
    Route::get('/theme-children/{id}/assets', [AdminThemeController::class, 'assetByChiId']);
    Route::post('/theme-children/{id}/assets', [AdminThemeController::class, 'addAsset']);
    Route::post('/theme-children/{id}/assets-from-library', [AdminThemeController::class, 'addAssetFromLibrary']);
    // Library assets
    Route::get('/library/assets', [Modules\DynamicTheme\Http\Controllers\Api\Admin\LibraryAssetController::class, 'index']);
    Route::post('/library/assets', [Modules\DynamicTheme\Http\Controllers\Api\Admin\LibraryAssetController::class, 'store']);
    Route::get('/library/assets/{id}', [Modules\DynamicTheme\Http\Controllers\Api\Admin\LibraryAssetController::class, 'show']);
    Route::delete('/library/assets/{id}', [Modules\DynamicTheme\Http\Controllers\Api\Admin\LibraryAssetController::class, 'destroy']);    // upload file for existing theme-asset
    Route::post('/theme-assets/{id}/file', [AdminThemeController::class, 'uploadAssetFile']);
    Route::delete('/theme-assets/{id}/file', [AdminThemeController::class, 'deleteAssetFile']);

    Route::post('/theme-assets', [AssetController::class, 'store']);
    Route::delete('/theme-assets/{id}', [AssetController::class, 'assetsDestroy']);
    Route::delete('/assets/{id}', [AssetController::class, 'destroy']);

    Route::get('screens/{screen}/allowed-widgets', [ScreenController::class, 'allowedWidgets']);
    Route::put('screens/{screen}/allowed-widgets', [ScreenController::class, 'updateAllowedWidgets']);

    // Asset Management
    Route::prefix('assets')->group(function () {
        Route::get('/', [AssetController::class, 'index']);
        Route::post('/', [AssetController::class, 'store']);
        Route::get('/{id}', [AssetController::class, 'show']);
        Route::delete('/{id}', [AssetController::class, 'destroy']);
    });

    // Import/Export Management
    Route::prefix('import-export')->group(function () {
        // Templates Download
        Route::get('/templates/widgets', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ImportExportController::class, 'downloadWidgetsTemplate']);
        Route::get('/templates/themes', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ImportExportController::class, 'downloadThemesTemplate']);

        // Export Data
        Route::get('/export/widgets', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ImportExportController::class, 'exportWidgets']);
        Route::get('/export/themes', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ImportExportController::class, 'exportThemes']);

        // Import Data
        Route::post('/import/widgets', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ImportExportController::class, 'importWidgets']);
        Route::post('/import/themes', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ImportExportController::class, 'importThemes']);

        // Validate Files
        Route::post('/validate/widgets', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ImportExportController::class, 'validateWidgetsFile']);
        Route::post('/validate/themes', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ImportExportController::class, 'validateThemesFile']);
    });

    // Client Configurations Management
    Route::prefix('configurations')->group(function () {
        Route::get('/', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ClientConfigurationController::class, 'index']);
        Route::post('/', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ClientConfigurationController::class, 'store']);
        Route::get('/{configuration}', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ClientConfigurationController::class, 'show']);
        Route::put('/{configuration}', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ClientConfigurationController::class, 'update']);
        Route::delete('/{configuration}', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ClientConfigurationController::class, 'destroy']);

        // Clone and Activate
        Route::post('/{configuration}/clone', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ClientConfigurationController::class, 'clone']);
        Route::post('/{configuration}/activate', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ClientConfigurationController::class, 'activate']);

        // Get full configuration for editing
        Route::get('/{configuration}/full', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ClientConfigurationController::class, 'getFullConfiguration']);

        // Update overrides
        Route::put('/{configuration}/screens', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ClientConfigurationController::class, 'updateScreenOverrides']);
        Route::put('/{configuration}/widgets', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ClientConfigurationController::class, 'updateWidgetOverrides']);

        // Child asset overrides (per configuration)
        Route::get('/{configuration}/children/{child}/assets', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ClientConfigurationController::class, 'getChildAssets']);
        Route::post('/{configuration}/child-asset-overrides/{override}/toggle', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ClientConfigurationController::class, 'toggleChildAssetVisibility']);

        // Asset overrides
        Route::post('/{configuration}/assets', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ClientConfigurationController::class, 'uploadAssetOverride']);
        Route::delete('/{configuration}/assets/{themeAssetId}', [Modules\DynamicTheme\Http\Controllers\Api\Dashboard\ClientConfigurationController::class, 'removeAssetOverride']);
    });
});

// ============================================================
// Admin API - For company admin panel (full CRUD)
// ============================================================
Route::prefix('admin')->group(function () {

    // Widget Management (full CRUD)
    Route::apiResource('widgets', AdminWidgetController::class);
    Route::get('/widgets/{widget}/settings', [AdminWidgetController::class, 'getSettings']);
    Route::post('/widgets/{widget}/settings', [AdminWidgetController::class, 'addSetting']);
    Route::put('/widgets/{widget}/settings/{setting}', [AdminWidgetController::class, 'updateSetting']);
    Route::delete('/widgets/{widget}/settings/{setting}', [AdminWidgetController::class, 'deleteSetting']);
    Route::get('/widgets/{widget}/actions', [AdminWidgetController::class, 'getActions']);
    Route::post('/widgets/{widget}/actions', [AdminWidgetController::class, 'addAction']);
    Route::put('/widgets/{widget}/actions/{action}', [AdminWidgetController::class, 'updateAction']);
    Route::delete('/widgets/{widget}/actions/{action}', [AdminWidgetController::class, 'deleteAction']);


    // Theme Management (full CRUD)
    Route::apiResource('themes', AdminThemeController::class);
    Route::post('/themes/{theme}/assets', [AdminThemeController::class, 'addAsset']);
    Route::put('/themes/{theme}/assets/{asset}', [AdminThemeController::class, 'updateAsset']);
    Route::put('/themes/{theme}/assets-dashboard/{asset}', [AdminThemeController::class, 'updateAssetDashboard']);
    Route::delete('/themes/{theme}/assets/{asset}', [AdminThemeController::class, 'deleteAsset']);

    // Asset File Upload
    Route::post('/upload', [Modules\DynamicTheme\Http\Controllers\Api\Admin\AssetUploadController::class, 'upload']);
    Route::post('/assets/{asset}/upload', [Modules\DynamicTheme\Http\Controllers\Api\Admin\AssetUploadController::class, 'uploadForAsset']);
    Route::delete('/assets/{asset}/file', [Modules\DynamicTheme\Http\Controllers\Api\Admin\AssetUploadController::class, 'delete']);

    // Screen Management (full CRUD)
    Route::apiResource('screens', AdminScreenController::class);
    Route::put('/screens/{screen}/widgets', [AdminScreenController::class, 'updateWidgets']);
    Route::get('/screens/{screen}/preview', [AdminScreenController::class, 'preview']);
    Route::post('/screens/{screen}/duplicate', [AdminScreenController::class, 'duplicate']);

    // Import/Export
    Route::post('/import', [AdminImportExportController::class, 'import']);
    Route::get('/export/widgets', [AdminImportExportController::class, 'exportWidgets']);
    Route::get('/export/themes', [AdminImportExportController::class, 'exportThemes']);
    Route::get('/export/template/{type}', [AdminImportExportController::class, 'downloadTemplate']);
});
