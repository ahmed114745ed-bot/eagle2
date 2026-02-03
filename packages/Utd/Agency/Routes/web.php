<?php

use Illuminate\Support\Facades\Route;
use Utd\Agency\Http\Controllers\Admin\AgencyController;
use Utd\Agency\Http\Controllers\Admin\PackageController;
use Utd\Agency\Http\Controllers\Admin\JoinRequestController;
use Utd\Agency\Http\Controllers\Admin\RecommendationAgencyController;
use Utd\Agency\Http\Controllers\Admin\RequestAgencyController;
use Utd\Agency\Http\Controllers\Admin\RequestAgencyFilterationController;
use Utd\Agency\Http\Controllers\Admin\SalaryController;
use Utd\Agency\Http\Controllers\Admin\AgencyMangerAgencyesController;
use Utd\Agency\Http\Controllers\Admin\AgencyMangerUsers;
use Utd\Agency\Http\Controllers\Admin\ChangeAgencyMangerController;
use Utd\Agency\Http\Controllers\Admin\UsersJoinedAgencyController;
use Utd\Agency\Http\Controllers\Admin\AgencyMangerTaregetController;
use Utd\Agency\Http\Controllers\Admin\AgencyControllers\HomeController;
use Utd\Agency\Http\Controllers\Admin\AgencyControllers\UserController;
use Utd\Agency\Http\Controllers\Admin\AgencyControllers\HostDiamondController;
use Utd\Agency\Http\Controllers\Admin\AgencyControllers\UserTargetController;
use Utd\Agency\Http\Controllers\Admin\AgencyControllers\AgencyTargetController;
use Utd\Agency\Http\Controllers\Admin\AgencyControllers\ChargeController;
use Utd\Agency\Http\Controllers\Admin\AgencyControllers\AgencyJoinRequestController;

Route::group([
    'prefix' => config('admin.route.prefix'),
    'middleware' => [
        'web',
        'admin',
        'adminIp',
        'multiLanguage',
    ],
    'as' => config('admin.route.prefix') . '.',
], function () {

    Route::group([
        'prefix' => 'agency',
        'as' => 'agency.',
    ], function () {
        Route::get('install', [PackageController::class, 'install'])->name('install');
        Route::get('uninstall', [PackageController::class, 'uninstall'])->name('uninstall');
        Route::get('debug', [PackageController::class, 'debug'])->name('debug');
    });

    Route::group(['middleware' => ['web-agency-feature']], function () {

        Route::resource('agencies', AgencyController::class);
        Route::get('agencies/profile/{id}', [AgencyController::class, 'profile'])->name('agency.profile');
        Route::post('agencies/accept_join/{id}', [AgencyController::class, 'acceptJoin']);
        Route::post('agencies/reject_join/{id}', [AgencyController::class, 'rejectJoin']);
        Route::post('agencies/admin/{id}', [AgencyController::class, 'adminAgency']);
        Route::post('agencies/kick/{id}', [AgencyController::class, 'kickFromAgency']);

        Route::resource('agencies-agency-manger', AgencyMangerAgencyesController::class);
        Route::resource('agency-manger-users', AgencyMangerUsers::class);
        Route::resource('agency-manger-target', AgencyMangerTaregetController::class);

        Route::resource('change_agencies_manger', ChangeAgencyMangerController::class);

        Route::resource('users-joined-agencies', UsersJoinedAgencyController::class);

        Route::resource('host-agencies', AgencyController::class);
        Route::get('host-agencies/profile/{id}', [AgencyController::class, 'profile'])->name('host-agencies.profile');
        Route::post('host-agencies/{id}/accept-join', [AgencyController::class, 'acceptJoin'])->name('host-agencies.accept-join');
        Route::post('host-agencies/{id}/reject-join', [AgencyController::class, 'rejectJoin'])->name('host-agencies.reject-join');
        Route::post('host-agencies/{id}/kick', [AgencyController::class, 'kick'])->name('host-agencies.kick');

        Route::resource('agency-join-requests', JoinRequestController::class)->only(['index']);
        Route::post('agency-join-requests/{id}/accept', [JoinRequestController::class, 'accept'])->name('agency-join-requests.accept');
        Route::post('agency-join-requests/{id}/reject', [JoinRequestController::class, 'reject'])->name('agency-join-requests.reject');

        Route::get('agency-salaries', [SalaryController::class, 'index'])->name('agency-salaries.index');
        Route::post('agency-salaries/{id}/mark-paid', [SalaryController::class, 'markAsPaid'])->name('agency-salaries.mark-paid');
        Route::get('agency-salaries/report', [SalaryController::class, 'report'])->name('agency-salaries.report');

        Route::prefix('ag')->name('agency.')->group(function () {
            Route::get('/', [HomeController::class, 'infoBox'])->name('home');
            Route::resource('/users', UserController::class);
            Route::get('professional/users', [UserController::class, 'indexProfessionals']);
            Route::get('/host-diamonds', [HostDiamondController::class, 'index'])->name('hsot-diamond');
            Route::get('/userTarget', [UserTargetController::class, 'index'])->name('userTarget');
            Route::get('/target', [AgencyTargetController::class, 'index'])->name('targets');
            Route::get('/charges', [ChargeController::class, 'index'])->name('charges');
            Route::resource('/ag-req', AgencyJoinRequestController::class);
        });

            Route::resource('request-agencies', RequestAgencyController::class);
            Route::resource('request-agencies-filteration', RequestAgencyFilterationController::class);
            Route::resource('recommendation-agencies', RecommendationAgencyController::class);
    }); // End of web-agency-feature group
});



