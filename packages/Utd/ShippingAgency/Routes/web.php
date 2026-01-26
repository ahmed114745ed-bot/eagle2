<?php

use Illuminate\Support\Facades\Route;
use Utd\ShippingAgency\Http\Controllers\Admin\AppearChargerAgencyController;
use Utd\ShippingAgency\Http\Controllers\Admin\ChargeAgencyController;
use Utd\ShippingAgency\Http\Controllers\Admin\MangerSettingController;
use Utd\ShippingAgency\Http\Controllers\Admin\PaymentGetWayController;

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
        Route::resource('charge-agencies', AppearChargerAgencyController::class);
        Route::get('shipping-agencies/profile/{id}', [AppearChargerAgencyController::class, 'shippingProfile'])->name('shipping.agency.profile');
        Route::get('charges/filter/{id}', [AppearChargerAgencyController::class, 'filterCharges'])->name('charges.filter');
        Route::resource('agency-country', ChargeAgencyController::class);
        Route::resource('payment-gateways', PaymentGetWayController::class);
        Route::get('/agency-setting-manger', [MangerSettingController::class, 'index']);
    }
);
