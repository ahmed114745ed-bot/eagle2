<?php

use App\Models\Room;
use App\Models\User;
use Modules\CP\Http\Controllers\web\CpVipController;
use Modules\CP\Http\Controllers\web\CPGiftController;
use Modules\CP\Http\Services\CpServices;

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
            'appFeatureEnable:cp'
        ],
        'as'         => config('admin.route.prefix') . '.',
    ],
    function (\Illuminate\Routing\Router $router) {
        $router->resource('cp-gifts', CPGiftController::class);
        $router->resource('cp-vips', CpVipController::class);
    });

    Route::get('/test-method-services', function () {
        $user = User::first();
        $room = Room::find(25);
        (new CpServices)->cancelation($user);
        dd("ddddddddddd");
        dd((new CpServices)->ranking());
     });
