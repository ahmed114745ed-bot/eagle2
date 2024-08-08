<?php

use Modules\Whatsapp\Http\Controllers\web\ConfigAppController;
use Modules\Whatsapp\Http\Controllers\web\WhatsappAppController;
use Modules\Whatsapp\Http\Controllers\web\WhatsappMessageController;

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
            'appFeatureEnable:whatsapp',
        ],
        'as'         => config('admin.route.prefix') . '.',
    ],
    function (\Illuminate\Routing\Router $router) {
        $router->resource('whatsapp-messages', WhatsappMessageController::class);
        $router->resource('whatsapp-apps', WhatsappAppController::class);
    });
