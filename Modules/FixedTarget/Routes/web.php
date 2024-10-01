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


Route::group(
    [
        'prefix'     => config('admin.route.prefix'),
        'namespace'  => 'Web',
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
        $router->resource('fixed-targets', FixedTargetController::class);
        $router->resource('special-users', SpecialUserController::class);
    });
