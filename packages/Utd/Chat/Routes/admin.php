<?php

use Illuminate\Support\Facades\Route;
use Utd\Chat\Http\Controllers\Admin\GroupChatController;
use Utd\Chat\Http\Controllers\Admin\GroupChatSettingController;

/*
|--------------------------------------------------------------------------
| Admin Routes for Chat Package
|--------------------------------------------------------------------------
|
| These routes are loaded by the ChatServiceProvider and are wrapped
| with the admin middleware and prefix.
|
*/

Route::prefix(config('admin.route.prefix', 'admin'))
    ->middleware(config('admin.route.middleware', ['web', 'admin']))
    ->group(function () {
        // Group Chat CRUD
        Route::resource('/group-chat', GroupChatController::class)->names([
            'index' => 'admin.group-chat.index',
            'create' => 'admin.group-chat.create',
            'store' => 'admin.group-chat.store',
            'show' => 'admin.group-chat.show',
            'edit' => 'admin.group-chat.edit',
            'update' => 'admin.group-chat.update',
            'destroy' => 'admin.group-chat.destroy',
        ]);

        // Chat Interface Routes
        Route::get('chat/view', [GroupChatController::class, 'chatView'])->name('admin.chat.view');
        Route::get('chat/messages', [GroupChatController::class, 'getMessages'])->name('admin.chat.messages');
        Route::post('chat/message', [GroupChatController::class, 'storeMessage'])->name('admin.chat.store');
        Route::put('chat/message', [GroupChatController::class, 'updateMessage'])->name('admin.chat.update');
        Route::delete('chat/message/{id}', [GroupChatController::class, 'deleteMessage'])->name('admin.chat.delete');
        Route::get('rooms/{id}/image', [GroupChatController::class, 'getRoomImage'])->name('admin.rooms.image');

        // Chat Settings Routes
        Route::get('setting-group-char', [GroupChatSettingController::class, 'index'])->name('admin.setting-group-char');
        Route::get('chat-settings', [GroupChatController::class, 'chat_settings'])->name('admin.chat-settings');
    });
