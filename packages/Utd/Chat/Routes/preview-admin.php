<?php

use Illuminate\Support\Facades\Route;
use Utd\Chat\Http\Controllers\Admin\GroupChatController;
use Utd\Chat\Http\Controllers\Admin\GroupChatSettingController;

/*
|--------------------------------------------------------------------------
| Preview Admin Routes for Chat Package
|--------------------------------------------------------------------------
|
| These routes are for the preview/demo admin panel.
|
*/

// Group Chat CRUD
Route::resource('/group-chat', GroupChatController::class);

// Chat Settings Routes
Route::get('setting-group-char', [GroupChatSettingController::class, 'index']);
Route::get('chat-settings', [GroupChatController::class, 'chat_settings']);
