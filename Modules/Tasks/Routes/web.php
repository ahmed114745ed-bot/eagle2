<?php

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
use Illuminate\Routing\Router;
use Modules\Tasks\Http\Controllers\DayController;
//use Modules\Tasks\Http\Controllers\DayController;
use Modules\Tasks\Http\Controllers\DailyTaskController;
use Modules\Tasks\Http\Controllers\UserDayProgressController;
use Modules\Tasks\Http\Controllers\UserDayTaskProgressController;
use Modules\Tasks\Http\Controllers\TaskRewardController;
use Modules\Tasks\Http\Controllers\UserTaskRewardController;


Route::group(
    [
        'prefix' => config('admin.route.prefix'),
        //'namespace' => 'web',
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
            'appFeatureEnable:achievement',
        ],
        'as' => config('admin.route.prefix') . '.',
    ],
    function (\Illuminate\Routing\Router $router) {
        $router->resource('days', DayController::class);
        $router->resource('daily-tasks', DailyTaskController::class);
        $router->resource('user-day-progresses', UserDayProgressController::class);
        $router->resource('user-day-task-progresses', UserDayTaskProgressController::class);
        $router->resource('task-rewards', TaskRewardController::class);
        $router->resource('user-task-rewards', UserTaskRewardController::class);
    });
