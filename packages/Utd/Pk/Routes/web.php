<?php

use Illuminate\Support\Facades\Route;
use Utd\Pk\Http\Controllers\Web\PkEventController;
use Utd\Pk\Http\Controllers\Web\PkEventGiftController;

/*
|--------------------------------------------------------------------------
| PK Web Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the PkServiceProvider within a group which
| contains the "web" middleware group for admin panel.
|
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
        Route::resource('pk-events', PkEventController::class);

        Route::prefix('pk-events-gift/{pk_type}/{pk_event_id}')->group(function () {
            Route::get('/', [PkEventGiftController::class, 'index']);
            Route::get('/{level}/create', [PkEventGiftController::class, 'create']);
            Route::post('/{level}', [PkEventGiftController::class, 'store']);
            Route::get('/{id}', [PkEventGiftController::class, 'show'])->where('id', '[0-9]+');
            Route::get('/{id}/edit', [PkEventGiftController::class, 'edit'])->where('id', '[0-9]+');
            Route::put('/{id}', [PkEventGiftController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [PkEventGiftController::class, 'destroy'])->where('id', '[0-9]+');
        });
    }
);
