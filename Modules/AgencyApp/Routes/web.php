<?php

use Modules\AgencyApp\Http\Controllers\web\RequestAgencyController;
use Modules\AgencyApp\Http\Controllers\web\RecommendationAgencyController;
use Modules\AgencyApp\Http\Controllers\web\RequestAgencyFilterationController;

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
            'authWeb',
            'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
        ],
        'as'         => config('admin.route.prefix') . '.',
    ],
    function (\Illuminate\Routing\Router $router) {
        $router->resource('request-agencies', RequestAgencyController::class);
        $router->resource('request-agencies-filteration', RequestAgencyFilterationController::class);
        $router->resource('recommendation-agencies', RecommendationAgencyController::class);
    });