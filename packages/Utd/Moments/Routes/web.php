<?php

use Utd\Moments\Http\Controllers\web\MomentController;
use Utd\Moments\Http\Controllers\web\MomentSettingsController;
use Utd\Moments\Http\Controllers\web\MomentViewerController;
use Utd\Moments\Http\Controllers\web\ReportMomentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('moment')->group(function () {
    Route::get('/', 'MomentController@index');
});

Route::get('delete-moment/{moment_id}/{id}', 'MomentController@destroy_dash')->name('delete-moment');

Route::group(
    [
        'prefix' => config('admin.route.prefix'),
        'namespace' => 'web',
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
        ],
        'as' => config('admin.route.prefix').'.',
    ],
    function () {
        Route::resource('report-moments', ReportMomentController::class);
        Route::post('moment-config', [MomentSettingsController::class, 'momentConfig'])->name('moment-config');
        Route::resource('moments', MomentController::class);
        Route::get('moment-gallery/{id}', [MomentController::class, 'momentGallery']);
        Route::resource('moment-settings', MomentSettingsController::class);

        // 🎯 Moment Viewer Routes (Facebook-like Experience)
        Route::prefix('moment-viewer')->name('moment-viewer.')->group(function () {
            Route::get('/', [MomentViewerController::class, 'index'])->name('index');
            Route::get('/viewer.css', [MomentViewerController::class, 'getViewerCss'])->name('viewer-css');
            Route::get('/api/moments', [MomentViewerController::class, 'getMoments'])->name('api.moments');
            Route::get('/api/users-with-moments', [MomentViewerController::class, 'getUsersWithMoments'])->name('api.users-with-moments');
            Route::get('/api/moment/{id}', [MomentViewerController::class, 'getMoment'])->name('api.moment');
            Route::get('/api/moment/{id}/likes', [MomentViewerController::class, 'getLikes'])->name('api.likes');
            Route::get('/api/moment/{id}/comments', [MomentViewerController::class, 'getComments'])->name('api.comments');
            Route::get('/api/moment/{id}/gifts', [MomentViewerController::class, 'getGifts'])->name('api.gifts');
            Route::put('/api/moment/{id}/description', [MomentViewerController::class, 'updateDescription'])->name('api.update-description');
            Route::delete('/api/comment/{id}', [MomentViewerController::class, 'deleteComment'])->name('api.delete-comment');
            Route::delete('/api/moment/{id}', [MomentViewerController::class, 'deleteMoment'])->name('api.delete-moment');
            Route::post('/api/reset-random', [MomentViewerController::class, 'resetRandomSeed'])->name('api.reset-random');
        });
    }
);
