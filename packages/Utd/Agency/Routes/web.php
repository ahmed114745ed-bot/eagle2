<?php

use Illuminate\Support\Facades\Route;
use Utd\Agency\Http\Controllers\Admin\AgencyController;
use Utd\Agency\Http\Controllers\Admin\JoinRequestController;
use Utd\Agency\Http\Controllers\Admin\SalaryController;

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
    
    // Agency CRUD
    Route::resource('host-agencies', AgencyController::class);
    
    // Agency profile
    Route::get('host-agencies/profile/{id}', [AgencyController::class, 'profile'])
        ->name('host-agencies.profile');
    
    // Accept/Reject join
    Route::post('host-agencies/{id}/accept-join', [AgencyController::class, 'acceptJoin'])
        ->name('host-agencies.accept-join');
    Route::post('host-agencies/{id}/reject-join', [AgencyController::class, 'rejectJoin'])
        ->name('host-agencies.reject-join');
    
    // Kick from agency
    Route::post('host-agencies/{id}/kick', [AgencyController::class, 'kick'])
        ->name('host-agencies.kick');
    
    // Join requests
    Route::resource('agency-join-requests', JoinRequestController::class)->only(['index']);
    Route::post('agency-join-requests/{id}/accept', [JoinRequestController::class, 'accept'])
        ->name('agency-join-requests.accept');
    Route::post('agency-join-requests/{id}/reject', [JoinRequestController::class, 'reject'])
        ->name('agency-join-requests.reject');
    
    // Salaries
    Route::get('agency-salaries', [SalaryController::class, 'index'])
        ->name('agency-salaries.index');
    Route::post('agency-salaries/{id}/mark-paid', [SalaryController::class, 'markAsPaid'])
        ->name('agency-salaries.mark-paid');
    Route::get('agency-salaries/report', [SalaryController::class, 'report'])
        ->name('agency-salaries.report');
});
