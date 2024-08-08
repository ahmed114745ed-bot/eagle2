<?php

use Illuminate\Http\Request;
use Modules\AgencyApp\Http\Controllers\AgencyAppController;
use Modules\AgencyApp\Http\Controllers\Api\AgencyAppController as ApiAgencyAppController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum', 'checkLatestToken', 'generalBan'])->group(function () {
    Route::post('create-agency',[AgencyAppController::class,'createAgency']);
    Route::post('action-request-agency',[AgencyAppController::class,'actionRequestAgency']);
    Route::get('all-agency-request',[AgencyAppController::class,'allAgencyRequest']);
    Route::get('agency-last-thirty-day',[\Modules\AgencyApp\Http\Controllers\Api\AgencyAppController::class,'agency_last_thirty_day']);
    Route::get('agency-total-reports',[\Modules\AgencyApp\Http\Controllers\Api\AgencyAppController::class,'agency_total_reports']);
    Route::post('cancel-request-createAgency',[\Modules\AgencyApp\Http\Controllers\Api\AgencyAppController::class,'cancel_request_createAgency']);
    Route::get('agency-request-info',[\Modules\AgencyApp\Http\Controllers\Api\AgencyAppController::class,'agency_request_info']);
    Route::get('user-agency-information', [\Modules\AgencyApp\Http\Controllers\Api\AgencyAppController::class, 'user_agency_information']);
    Route::prefix('agencies')->group(function () {
        Route::post('request-leave-agency', [ApiAgencyAppController::class, 'leave_agency']);
        Route::post('history-data-agency', [ApiAgencyAppController::class, 'historyDataAgency']);
        Route::post('make-user-as-operator', [ApiAgencyAppController::class, 'make_user_handling_requests']);
        Route::post('kick-of-agency', [ApiAgencyAppController::class, 'kick_of_agency']);
        Route::post('/filter', [ApiAgencyAppController::class, 'agency_filter']);
        Route::post('host-reports', [ApiAgencyAppController::class, 'dailyReport']);


    });
    
    //  samule dashboard
    Route::get('agency-data',[AgencyAppController::class,'agency_data']);
    Route::get('host-report/{id}',[AgencyAppController::class,'host_report']);
    Route::post('host-daily-report',[AgencyAppController::class,'host_daily_report']);
    Route::post('/host-daily-export-data', [AgencyAppController::class,'host_daily_export_data']);
    Route::post('/invite-user-to-hostAgency', [AgencyAppController::class,'invite_user_to_hostAgency']);
    Route::get('/get-invite-agency', [AgencyAppController::class,'agencyHostInvitation']);
    Route::get('/action-invite-agency', [AgencyAppController::class,'actionInvitation']);

});

 