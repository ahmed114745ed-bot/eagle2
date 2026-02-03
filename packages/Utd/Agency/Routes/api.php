<?php

use Utd\Agency\Http\Controllers\Admin\AgencySettingsController;
use Illuminate\Support\Facades\Route;
use Utd\Agency\Http\Controllers\Admin\AgencyControllers\ChargeController as AdminChargeController;
use Utd\Agency\Http\Controllers\Api\ChargeController;
use Utd\Agency\Http\Controllers\Admin\AgencyControllers\UserController;
use Utd\Agency\Http\Controllers\Api\AgencyController;
use Utd\Agency\Http\Controllers\Api\V2\AgencyController as V2AgencyController;
use Utd\Agency\Http\Controllers\Api\V2\Dashboard\AgencyHostInviteController;
use Utd\Agency\Http\Controllers\Shipping\Admin\PaymentGetWayController;
use Utd\Agency\Http\Controllers\Api\AgencyAppController;
use Utd\Agency\Http\Controllers\Api\V2\AgencyAppController as ApiAgencyAppController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'checkLatestToken', 'generalBan', 'appFeatureEnable:agencies', 'update.last.seen'])
    ->prefix('agencies')
    ->group(function () {
        
        Route::get('/', [AgencyController::class, 'activeAgencies']);
        Route::get('/history', [AgencyController::class, 'history']);
        Route::get('/{id}', [AgencyController::class, 'show'])->where('id', '[0-9]+');
        Route::get('/{id}/members', [AgencyController::class, 'members'])->where('id', '[0-9]+');
        Route::get('/{id}/target', [AgencyController::class, 'target'])->where('id', '[0-9]+');
        Route::get('/{id}/stars', [AgencyController::class, 'stars'])->where('id', '[0-9]+');
        Route::get('/{id}/heroes', [AgencyController::class, 'heroes'])->where('id', '[0-9]+');
        Route::post('/join', [AgencyController::class, 'joinRequest']);
        Route::post('/leave', [AgencyController::class, 'leaveAgency']);




        Route::post('create-agency', [AgencyAppController::class, 'createAgency']);
        Route::post('action-request-agency', [AgencyAppController::class, 'actionRequestAgency']);
        Route::get('all-agency-request', [AgencyAppController::class, 'allAgencyRequest']);
        Route::get('agency-last-thirty-day', [ApiAgencyAppController::class, 'agency_last_thirty_day']);
        Route::get('agency-total-reports', [ApiAgencyAppController::class, 'agency_total_reports']);
        Route::post('cancel-request-createAgency', [AgencyAppController::class, 'cancel_request_createAgency']);
        Route::get('agency-request-info', [AgencyAppController::class, 'agency_request_info']);
          
        
        Route::post('request-leave-agency', [ApiAgencyAppController::class, 'leave_agency']);
        Route::post('history-data-agency', [ApiAgencyAppController::class, 'historyDataAgency']);
        Route::post('kick-of-agency', [ApiAgencyAppController::class, 'kick_of_agency']);
        Route::post('/filter', [ApiAgencyAppController::class, 'agency_filter']);
        Route::post('host-reports', [ApiAgencyAppController::class, 'dailyReport']);//host center
    

        //dashboard
        Route::get('agency-data', [ApiAgencyAppController::class, 'agency_data']);
        Route::get('host-report/{id}', [ApiAgencyAppController::class, 'host_report']);
        Route::post('host-daily-report', [ApiAgencyAppController::class, 'host_daily_report']);
        Route::post('/host-daily-export-data', [ApiAgencyAppController::class, 'host_daily_export_data']);
        Route::post('/invite-user-to-hostAgency', [AgencyHostInviteController::class, 'invite_user_to_hostAgency']);
        Route::get('/get-invite-agency', [AgencyHostInviteController::class, 'agencyHostInvitation']);
        Route::post('/action-invite-agency', [AgencyHostInviteController::class, 'actionInvitation']);
        Route::post('/host-agency-edit', [ApiAgencyAppController::class, 'host_agency_edit']);
   
                Route::post('charge_co_for_users', [ChargeController::class, 'sendMoneyFoeHost']);
                Route::get('charge_co_for_usersHistory', [ChargeController::class, 'chargeCoForUsersHistory']);
                Route::post('charge_dollar_for_owner', [ChargeController::class, 'ChargeDollarForOwner']);
                Route::get('charge_dollar_for_OwnerHistory', [ChargeController::class, 'chargeDollarHistory']);
                Route::post('join_request', [V2AgencyController::class, 'joinRequest']);
                Route::get('show', [V2AgencyController::class, 'view']);
                Route::get('details/{id}', [V2AgencyController::class, 'agencyDetails']);
                Route::get('admins/{id}', [V2AgencyController::class, 'admin']);
                Route::get('target-details/{id}', [V2AgencyController::class, 'agencyTargetDetails']); //target
                Route::get('stars/{id}', [V2AgencyController::class, 'star']);
                Route::get('heroes/{id}', [V2AgencyController::class, 'heroes']);
                Route::post('showAllusers', [V2AgencyController::class, 'agencyMembers']);
                Route::get('show-agency-request', [V2AgencyController::class, 'showAgencyRequest']);
                Route::get('show_request', [V2AgencyController::class, 'show_request']);
                Route::post('actions_request', [V2AgencyController::class, 'Accept_request']);
                Route::get('list_options_his', [V2AgencyController::class, 'list_options_his']);
                Route::post('historyAgancy', [V2AgencyController::class, 'historyAgencySearch']);
                Route::post('make-user-as-operator', [V2AgencyController::class, 'make_user_handling_requests']);
                Route::post('charge_to', [ChargeController::class, 'chargeTo']);
                Route::post('charges-history', [ChargeController::class, 'chargeToHistory']);
                Route::get('history/{id}', [V2AgencyController::class, 'history']);
                Route::post('{id}', [V2AgencyController::class, 'update'])->where('id', '[0-9]+');
                Route::get('charges', [V2AgencyController::class, 'agenciesCharge']);
                Route::post('charge-agency', [ChargeController::class, 'chargeFromAgencyToAnother']);
                Route::get('old-agencies', [V2AgencyController::class, 'gitOldAgencies']);

            Route::post('search-user-agency', [ChargeController::class, 'getUserAgency']);
    
   
   
            Route::get('/agency-badges', [AgencySettingsController::class, 'badges']);
            Route::get('user-agency-information', [AgencyAppController::class, 'user_agency_information']);

   
   
   
        });
