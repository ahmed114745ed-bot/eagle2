<?php

use Utd\Events\Http\Controllers\web\TargetEventController;
use Utd\Events\Http\Controllers\web\RewardTargetController;
use Utd\Events\Http\Controllers\web\WeeklyEventGiftNController;
use Utd\Events\Http\Controllers\web\EventPeriodController;
use Utd\Events\Http\Controllers\web\WeeklyEventNController;
use Utd\Events\Http\Controllers\web\GeneralRoleController;
use Utd\Events\Http\Controllers\web\EventReportController;
use Utd\Events\Entities\WeeklyStar;
use Illuminate\Support\Facades\Route;

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


Route::group(
    [
        'prefix'     => config('admin.route.prefix'),
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
        ],
        'as'         => config('admin.route.prefix') . '.',
    ],
    function () {
        Route::resource('event-period', EventPeriodController::class);
        Route::resource('weekly-events-new', WeeklyEventNController::class);
        Route::resource('target-events', TargetEventController::class);
        Route::prefix('weekly-events-gift/{weekly_event_id}')->group(function () {
            Route::get('/', [WeeklyEventGiftNController::class, 'index']);
            Route::get('/{level}/create', [WeeklyEventGiftNController::class, 'create']);
            Route::post('/{level}', [WeeklyEventGiftNController::class, 'store']);
            Route::get('/{id}', [WeeklyEventGiftNController::class, 'show'])->where('id', '[0-9]+');
            Route::get('/{id}/edit', [WeeklyEventGiftNController::class, 'edit'])->where('id', '[0-9]+');
            Route::put('/{id}', [WeeklyEventGiftNController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [WeeklyEventGiftNController::class, 'destroy'])->where('id', '[0-9]+');
        });

        Route::delete('target-events-gift/{id}/{targets}', [RewardTargetController::class, 'destroyBulk'])
        ->where('targets', '.*');
        Route::prefix('target-events-gift/{charge_event_id}')->group(function () {
            Route::get('/', [RewardTargetController::class, 'index']);
            Route::get('/create', [RewardTargetController::class, 'create']);
            Route::post('/', [RewardTargetController::class, 'store']);
            Route::get('/{id}', [RewardTargetController::class, 'show'])->where('id', '[0-9]+');
            Route::get('/{id}/edit', [RewardTargetController::class, 'edit'])->where('id', '[0-9]+');
            Route::put('/{id}', [RewardTargetController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [RewardTargetController::class, 'destroy'])->where('id', '[0-9]+');
        });

        Route::resource('general-rols', GeneralRoleController::class);
        Route::resource('event-reports', EventReportController::class);
        Route::get("update-weekly-star", function () {
            WeeklyStar::whereNull('type')->update([
                'type' => "weekly_star"
            ]);
        });
    }
);
