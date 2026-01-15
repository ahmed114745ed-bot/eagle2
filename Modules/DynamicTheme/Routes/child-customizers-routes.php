import ChildCustomizerController from './Http/Controllers/Api/ChildCustomizerController.php';

/**
 * Child Customizers
 */
Route::prefix('child-customizers')
    ->controller(ChildCustomizerController::class)
    ->group(function () {
        // Get all child customizers for a widget override
        Route::get('widget-override/{configWidgetOverrideId}', 'indexByWidgetOverride')
            ->name('child-customizers.index-by-widget');
        
        // CRUD operations
        Route::get('{id}', 'show')->name('child-customizers.show');
        Route::post('/', 'store')->name('child-customizers.store');
        Route::put('{id}', 'update')->name('child-customizers.update');
        Route::delete('{id}', 'destroy')->name('child-customizers.destroy');
        
        // Generate CSS
        Route::get('{id}/css', 'generateCSS')->name('child-customizers.css');
        
        // Batch operations
        Route::post('batch-update', 'batchUpdate')->name('child-customizers.batch-update');
    });
