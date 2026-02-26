<?php

use Illuminate\Support\Facades\Route;
use Utd\SwitchAccount\Http\Controllers\SwitchAccountController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'checkLatestToken', 'generalBan'])->group(function () {
    Route::post('add-account', [SwitchAccountController::class, 'add_account']);
    Route::post('switch-account', [SwitchAccountController::class, 'switch_account']);
    Route::get('my-accounts', [SwitchAccountController::class, 'myAccounts']);
});
