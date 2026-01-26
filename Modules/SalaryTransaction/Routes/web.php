<?php

use Illuminate\Support\Facades\Route;
use Modules\SalaryTransaction\Http\Controllers\AgentRequestController;
use Modules\SalaryTransaction\Http\Controllers\ChargeCountryController;
use Modules\SalaryTransaction\Http\Controllers\SalaryRequestController;
use Modules\SalaryTransaction\Http\Controllers\RequestProblemController;
use Modules\SalaryTransaction\Http\Controllers\AgentRequestHistoryController;

Route::group(
    [
        'prefix'        => config('admin.route.prefix'),
        'middleware'    => [
            'web',
            'admin',
            'adminIp',
            // 'adminGeneralBan',
            'multiLanguage',
            'web-agency-feature'
        ],
        'as' => config('admin.route.prefix') . '.',
    ],
    function () {
        Route::resource('transaction-request-problem', RequestProblemController::class);
        Route::resource('agent-salary-requests', AgentRequestController::class);
        Route::resource('charge-country', ChargeCountryController::class);
        Route::resource('salary-requests', SalaryRequestController::class);
        Route::resource('agent-requests-history', AgentRequestHistoryController::class);
    }
);
