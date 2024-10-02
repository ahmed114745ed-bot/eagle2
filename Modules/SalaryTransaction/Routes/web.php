<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Modules\SalaryTransaction\Http\Controllers\AgentRequestController;
use Modules\SalaryTransaction\Http\Controllers\ChargeCountryController;
use Modules\SalaryTransaction\Http\Controllers\SalaryRequestController;
use Modules\SalaryTransaction\Http\Controllers\RequestProblemController;
use Modules\SalaryTransaction\Http\Controllers\AgentRequestHistoryController;
use Modules\SalaryTransaction\Http\Controllers\ChargeAgencyController;

Route::group(
    [
        'prefix'        => config('admin.route.prefix'),
        'middleware'    => [
            'web',
            'admin',
            'adminIp',
//            'adminGeneralBan',
            'multiLanguage',
        ],
        'as'            => config('admin.route.prefix') . '.',
    ],
    function (Router $router) {
        $router->resource ('transaction-request-problem',RequestProblemController::class);
        $router->resource ('agent-salary-requests',AgentRequestController::class);
        $router->resource ('charge-country',ChargeCountryController::class);
        $router->resource ('agency-country',ChargeAgencyController::class);
        $router->resource('salary-requests', SalaryRequestController::class);
        $router->resource ('agent-requests-history', AgentRequestHistoryController::class);
       
});
