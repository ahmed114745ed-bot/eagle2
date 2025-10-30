<?php

use Illuminate\Support\Facades\Route;
use Modules\Form\Http\Controllers\FormRequestController;
use Modules\Form\Http\Controllers\FormSubmissionController;
use Modules\Form\Http\Controllers\FormTemplateController;



/*
*
* #########################   use
*
**/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/forms', [FormTemplateController::class, 'showByType'])
        ->name('forms.showByType');
    Route::post('/forms/{type}', [FormTemplateController::class, 'storeSubmission'])
        ->name('form.submit');
    Route::get('/form-translations', [FormTemplateController::class, 'getTranslations']);
});

 Route::group(
    [
        'prefix'     => config('admin.route.prefix'),
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            'multiLanguage',
        ],
        'as'         => config('admin.route.prefix') . 'routes',
    ],
    function () {
 
 
        Route::resource('form-templates', FormTemplateController::class);
        Route::resource('form-requests', FormRequestController::class);
        Route::prefix('requests')->group(function () {
            Route::post('{id}/approve', [FormRequestController::class, 'approve'])->name('requests.approve');
            Route::post('{id}/reject', [FormRequestController::class, 'reject'])->name('requests.reject');
        });
   
    }
);

/*
*
* #########################  end use
*
**/




// Route::get('/', function () {
//     return view('welcome');
// })->name('home');

// Public Routes - Form Display & Submission
Route::get('/forms/{template}', [FormSubmissionController::class, 'show'])->name('forms.show');
Route::post('/forms/{template}/submit', [FormSubmissionController::class, 'submit'])->name('forms.submit');
Route::get('/forms/success/{submission}', [FormSubmissionController::class, 'success'])->name('forms.success');

// Admin Routes - Submissions Management
Route::get('/submissions', [FormSubmissionController::class, 'index'])->name('submissions.index');
Route::get('/submissions/{submission}', [FormSubmissionController::class, 'view'])->name('submissions.view');
Route::patch('/submissions/{submission}/status', [FormSubmissionController::class, 'updateStatus'])->name('submissions.updateStatus');
Route::delete('/submissions/{submission}', [FormSubmissionController::class, 'destroy'])->name('submissions.destroy');


