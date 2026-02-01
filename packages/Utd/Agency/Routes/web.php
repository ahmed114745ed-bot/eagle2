<?php

use Illuminate\Support\Facades\Route;
use Utd\Agency\Http\Controllers\Admin\AgencyController;
use Utd\Agency\Http\Controllers\Admin\PackageController;
use Utd\Agency\Http\Controllers\Admin\JoinRequestController;
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

/*
|--------------------------------------------------------------------------
| Web Routes (Admin Panel)
|--------------------------------------------------------------------------
*/

Route::group([
    'prefix' => config('admin.route.prefix'),
    'middleware' => [
        'web',
        'admin',
        'adminIp',
        'multiLanguage',
        'web-agency-feature',
    ],
    'as' => config('admin.route.prefix') . '.',
], function () {

    // Package Management

    if (\App\Helpers\AgencyPackageHelper::isAgencyInstalled()) {
        // Main Agency CRUD
        Route::resource('agencies', AgencyController::class)->middleware('web-agency-feature');
        Route::get('agencies/profile/{id}', [AgencyController::class, 'profile'])->name('agency.profile');
        Route::post('agencies/accept_join/{id}', [AgencyController::class, 'acceptJoin']);
        Route::post('agencies/reject_join/{id}', [AgencyController::class, 'rejectJoin']);
        Route::post('agencies/admin/{id}', [AgencyController::class, 'adminAgency']);
        Route::post('agencies/kick/{id}', [AgencyController::class, 'kickFromAgency']);

        // Agency Manager Routes
        Route::resource('agencies-agency-manger', AgencyMangerAgencyesController::class);
        Route::resource('agency-manger-users', AgencyMangerUsers::class);
        Route::resource('agency-manger-target', AgencyMangerTaregetController::class);

        // Change agency manager
        Route::resource('change_agencies_manger', ChangeAgencyMangerController::class);

        // Users joined agencies
        Route::resource('users-joined-agencies', UsersJoinedAgencyController::class)->middleware('web-agency-feature');

        // Host agencies alias
        Route::resource('host-agencies', AgencyController::class);
        Route::get('host-agencies/profile/{id}', [AgencyController::class, 'profile'])->name('host-agencies.profile');
        Route::post('host-agencies/{id}/accept-join', [AgencyController::class, 'acceptJoin'])->name('host-agencies.accept-join');
        Route::post('host-agencies/{id}/reject-join', [AgencyController::class, 'rejectJoin'])->name('host-agencies.reject-join');
        Route::post('host-agencies/{id}/kick', [AgencyController::class, 'kick'])->name('host-agencies.kick');

        // Join requests
        Route::resource('agency-join-requests', JoinRequestController::class)->only(['index']);
        Route::post('agency-join-requests/{id}/accept', [JoinRequestController::class, 'accept'])->name('agency-join-requests.accept');
        Route::post('agency-join-requests/{id}/reject', [JoinRequestController::class, 'reject'])->name('agency-join-requests.reject');

        // Salaries
        Route::get('agency-salaries', [SalaryController::class, 'index'])->name('agency-salaries.index');
        Route::post('agency-salaries/{id}/mark-paid', [SalaryController::class, 'markAsPaid'])->name('agency-salaries.mark-paid');
        Route::get('agency-salaries/report', [SalaryController::class, 'report'])->name('agency-salaries.report');

        // Agency Controllers Group (ag prefix)
        Route::prefix('ag')->name('agency.')->middleware('web-agency-feature')->group(function () {
            Route::get('/', [HomeController::class, 'infoBox'])->name('home');
            Route::resource('/users', UserController::class);
            Route::get('professional/users', [UserController::class, 'indexProfessionals']);
            Route::get('/host-diamonds', [HostDiamondController::class, 'index'])->name('hsot-diamond');
            Route::get('/userTarget', [UserTargetController::class, 'index'])->name('userTarget');
            Route::get('/target', [AgencyTargetController::class, 'index'])->name('targets');
            Route::get('/charges', [ChargeController::class, 'index'])->name('charges');
            Route::resource('/ag-req', AgencyJoinRequestController::class);
        });
    }
});



