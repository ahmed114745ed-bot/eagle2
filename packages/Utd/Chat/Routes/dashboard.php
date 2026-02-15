<?php

use Illuminate\Support\Facades\Route;
use Utd\Chat\Http\Controllers\Dashboard\AdminGroupChatController;

Route::middleware(['auth:sanctum', 'verified', 'localization', 'update.last.seen'])->prefix('api/dashboard')->group(function () {
    Route::resource('admin-GroupChat', AdminGroupChatController::class);
});
