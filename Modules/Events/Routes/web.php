<?php

use Modules\Events\Http\Controllers\web\PkEventController;
use Modules\Events\Http\Controllers\web\PkEventGiftController;
use Modules\Events\Http\Controllers\web\TargetEventController;
use Modules\Events\Http\Controllers\web\RewardTargetController;
use Modules\Events\Http\Controllers\web\WeeklyEventGiftNController;



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
        'namespace'  => 'web',
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
        ],
        'as'         => config('admin.route.prefix') . '.',
    ],
    function (\Illuminate\Routing\Router $router) {
        $router->resource ('event-period','EventPeriodController');
        $router->resource ('weekly-events-new','WeeklyEventNController');
        $router->resource('target-events', TargetEventController::class);
        $router->resource('pk-events', PkEventController::class);
        Route::prefix('weekly-events-gift/{weekly_event_id}')->group(function () {
            Route::get('/', [WeeklyEventGiftNController::class, 'index']);
            Route::get('/{level}/create', [WeeklyEventGiftNController::class, 'create']);
            Route::post('/{level}', [WeeklyEventGiftNController::class, 'store']);
            Route::get('/{id}', [WeeklyEventGiftNController::class, 'show'])->where('id', '[0-9]+');
            Route::get('/{id}/edit', [WeeklyEventGiftNController::class, 'edit'])->where('id', '[0-9]+');
            Route::put('/{id}', [WeeklyEventGiftNController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [WeeklyEventGiftNController::class, 'destroy'])->where('id', '[0-9]+');
        });

        Route::prefix('pk-events-gift/{pk_type}/{pk_event_id}')->group(function () {
            Route::get('/', [PkEventGiftController::class, 'index']);
            Route::get('/{level}/create', [PkEventGiftController::class, 'create']);
            Route::post('/{level}', [PkEventGiftController::class, 'store']);
            Route::get('/{id}', [PkEventGiftController::class, 'show'])->where('id', '[0-9]+');
            Route::get('/{id}/edit', [PkEventGiftController::class, 'edit'])->where('id', '[0-9]+');
            Route::put('/{id}', [PkEventGiftController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [PkEventGiftController::class, 'destroy'])->where('id', '[0-9]+');
        });


        Route::prefix('target-events-gift/{charge_event_id}')->group(function () {
            Route::get('/', [RewardTargetController::class, 'index']);
            Route::get('/create', [RewardTargetController::class, 'create']);
            Route::post('/', [RewardTargetController::class, 'store']);
            Route::get('/{id}', [RewardTargetController::class, 'show'])->where('id', '[0-9]+');
            Route::get('/{id}/edit', [RewardTargetController::class, 'edit'])->where('id', '[0-9]+');
            Route::put('/{id}', [RewardTargetController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [RewardTargetController::class, 'destroy'])->where('id', '[0-9]+');
        });

        $router->resource ('general-rols','GeneralRoleController');
        $router->resource ('event-reports','EventReportController');
        Route::get("update-weekly-star",function (){
            \Modules\Events\Entities\WeeklyStar::whereNull('type')->update([
                'type'=>"weekly_star"
            ]);
        });
    });

