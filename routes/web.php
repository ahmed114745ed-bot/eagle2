<?php

use App\Models\DeleteAccount;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use App\Admin\Controllers\CoinController;
use App\Admin\Controllers\UserController;
use App\Http\Controllers\addTOjesonController;
use App\Http\Controllers\Api\V1\ConfigController;

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


Route::prefix('payment')->group(function () {
    Route::get('payment-success', [\App\Http\Controllers\Web\PaymentController::class, 'success']);
    Route::get('payment-fail', [\App\Http\Controllers\Web\PaymentController::class, 'fail']);
});

Route::get('/page/{name}', function ($name) {
    $page = \App\Models\Page::query()->where('name', $name)->firstOrFail();
    return (app()->getLocale() == 'ar' ? $page->content : ($page->content_en ?? $page->content));
})->middleware('localization');

Route::get('/clear', function () {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('config:cache');
    Artisan::call('view:cache');

    if (config('app.env') == 'production') {
        Artisan::call('route:cache');
    }

    return "Cleared!";
});


Route::get('/admin/custom-export-users', [
    \App\Admin\Controllers\ExportController::class,
    'usersSallaryTargets'
])->name('custom-export-users');

Route::get('/admin/agency-export-report', [
    \App\Admin\Controllers\ExportController::class,
    'usersAgencyTargets'
])->name('agency-export-report');


Route::get('/privacy-policy', function () {
    $page = \App\Models\Page::where("name", "privacy-policy")->first();
    return view('privacy.privacy', ['page' => $page]);
});

Route::get('delete-account', function () {
    $data = DeleteAccount::get();
    return view('deleteAccount', compact("data"));
});




Route::get('/', function () {
    return response()->json();
});




Route::group(
    [
        'prefix' => config('admin.route.prefix'),
        'namespace' => config('admin.route.namespace'),
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
        ],
        'as' => config('admin.route.prefix') . '.',
    ],
    function (Router $router) {
        Route::post('postAddSitin', [addTOjesonController::class, 'postAddSitin'])->name('postAddSitin');
        Route::post('update-config-group-chat', [ConfigController::class, 'updateConfigChatGroup'])->name('update-config-group-chat');
        Route::post('update-agora-zego', [ConfigController::class, 'updateConfigAgoraZego'])->name('update-agora-zego');
        Route::post("send-request-make-rooms-top", [UserController::class, "make_rooms_top"]);
        Route::post("send-request-transfer-salary", [UserController::class, "transferSalary"]);
        Route::post("send-request-stop-charge", [UserController::class, "stop_charge"]);


    }
);
Route::group(
    [
        'prefix' => '',
        'namespace' => '',
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
        ],
        'as' => '',
    ],
    function (Router $router) {
        $router->get('admin/auth', function () {
            return view('checkLogin');
        })->name('admin/auth');
        $router->get('admin/config/auth', function () {
            return view('configLogin');
        })->name('config/auth');
        $router->post('/authenticate', [\App\Admin\Controllers\GameChargeHistoryController::class, 'chickLogin'])->name('authenticate');
        $router->post('/config-authenticate', [\App\Admin\Controllers\ConfigController::class, 'chickLogin'])->name('config-authenticate');


    }
);
