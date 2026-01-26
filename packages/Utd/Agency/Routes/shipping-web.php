<?php

use Illuminate\Support\Facades\Route;
use Utd\Agency\Http\Controllers\Shipping\Admin\AppearChargerAgencyController;
use Utd\Agency\Http\Controllers\Shipping\Admin\ChargeAgencyController;
use Utd\Agency\Http\Controllers\Shipping\Admin\MangerSettingController;
use Utd\Agency\Http\Controllers\Shipping\Admin\PaymentGetWayController;

/*
|--------------------------------------------------------------------------
| Shipping Agency Web Routes
|--------------------------------------------------------------------------
*/

Route::group(
    [
        'prefix'     => config('admin.route.prefix'),
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            'multiLanguage',
        ],
        'as'         => config('admin.route.prefix') . '.',
    ],
    function () {
        Route::resource('charge-agencies', AppearChargerAgencyController::class);
        Route::get('shipping-agencies/profile/{id}', [AppearChargerAgencyController::class, 'shippingProfile'])->name('shipping.agency.profile');
        Route::get('charges/filter/{id}', [AppearChargerAgencyController::class, 'filterCharges'])->name('charges.filter');
        Route::resource('agency-country', ChargeAgencyController::class);
        Route::resource('payment-gateways', PaymentGetWayController::class);
        Route::get('/agency-setting-manger', [MangerSettingController::class, 'index']);
        Route::post('/agency-setting-manger/save', [MangerSettingController::class, 'save'])->name('agency-manger-setting.save');
    }
);
