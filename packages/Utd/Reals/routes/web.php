<?php

use Illuminate\Support\Facades\Route;
use Utd\Reals\Http\Controllers\Web\AdminReelController;
use Utd\Reals\Http\Controllers\Web\ReelController;
use Utd\Reals\Http\Controllers\Web\ReelSettingsController;
use Utd\Reals\Http\Controllers\Web\ReportRealsController;
use Utd\Reals\Entities\Real;
use Utd\Reals\Entities\ReportReals;

/*
|--------------------------------------------------------------------------
| Reals Web Routes (Admin Dashboard)
|--------------------------------------------------------------------------
*/

// Laravel-Admin Routes
Route::prefix('admin')
    ->middleware(['web', 'admin'])
    ->group(function () {
        // Report Reals Resource
        Route::resource('report-reals', ReportRealsController::class);

        // Reels Resource (Grid Management)
        Route::resource('reels', ReelController::class);

        // Reels Settings Resource
        Route::resource('reels-settings', ReelSettingsController::class);

        // Custom Admin Reels View Routes
        Route::prefix('view')->name('admin.reels.')->group(function () {
            // Main reels view dashboard
            Route::get('reels', [AdminReelController::class, 'index'])->name('index');

            // Load more reels (AJAX)
            Route::get('reels/load-more', [AdminReelController::class, 'loadMore'])->name('loadMore');

            // Get single reel details
            Route::get('reels/{id}', [AdminReelController::class, 'show'])->name('show');

            // Get likes for a reel
            Route::get('reels/{id}/likes', [AdminReelController::class, 'getLikes'])->name('likes');

            // Get comments for a reel
            Route::get('reels/{id}/comments', [AdminReelController::class, 'getComments'])->name('comments');

            // Get gifts for a reel
            Route::get('reels/{id}/gifts', [AdminReelController::class, 'getGifts'])->name('gifts');

            // Batch counts (for multiple reels)
            Route::post('reels/batch-counts', [AdminReelController::class, 'batchCounts'])->name('batchCounts');

            // Update reel
            Route::put('reels/{id}', [AdminReelController::class, 'update'])->name('update');

            // Delete reel
            Route::delete('reels/{id}', [AdminReelController::class, 'destroy'])->name('destroy');
        });

        // Delete Reel via Report (Named route for ReportRealsController)
        Route::get('delete-reel/{real_id}/{id}', function ($realId, $reportId) {
            $real = Real::find($realId);
            if ($real) {
                $real->likes()->delete();
                $real->comments()->delete();
                $real->views()->delete();
                $real->delete();
            }

            $report = ReportReals::find($reportId);
            if ($report) {
                $report->delete();
            }

            return redirect()->route('report-reals.index')
                ->with('success', 'تم حذف الريل والبلاغ بنجاح');
        })->name('delete-reel');
    });
