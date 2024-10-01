<?php

use Illuminate\Support\Facades\Route;
use Modules\WhatsappAuth\Http\Controllers\WhatsappAuthController;

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
        $router->resource('whatsapp-messages', WhatsappMessageController::class);
    });
