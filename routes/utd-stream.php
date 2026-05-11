<?php

use App\Http\Controllers\Api\V1\UtdStreamController;
use Illuminate\Support\Facades\Route;

// ─── UTD-STREAM ─────────────────────────────────
Route::prefix('stream')->group(function () {
    // ─── Authentication ─────────────────────────
    Route::post('token', [UtdStreamController::class, 'token']);
    Route::get('credential', [UtdStreamController::class, 'credential']);

    // ─── Rooms Management ───────────────────────
    Route::get('rooms', [UtdStreamController::class, 'getRooms']);
    Route::get('rooms/{roomName}', [UtdStreamController::class, 'roomInfo']);
    Route::delete('rooms/{roomName}', [UtdStreamController::class, 'closeRoom']);
    Route::put('rooms/{roomName}/metadata', [UtdStreamController::class, 'updateRoomMetadata']);
    Route::post('rooms/{roomName}/send-data', [UtdStreamController::class, 'sendData']);

    // ─── Participant Management ─────────────────
    Route::get('rooms/{roomName}/participants/{identity}', [UtdStreamController::class, 'participantInfo']);
    Route::delete('rooms/{roomName}/participants/{identity}', [UtdStreamController::class, 'kick']);
    Route::put('rooms/{roomName}/participants/{identity}/permissions', [UtdStreamController::class, 'updateParticipantPermissions']);
    Route::put('rooms/{roomName}/participants/{identity}/metadata', [UtdStreamController::class, 'setParticipantMetadata']);
    Route::put('rooms/{roomName}/participants/{identity}/mute', [UtdStreamController::class, 'mute']);
    Route::put('rooms/{roomName}/participants/{identity}/forbid-stream', [UtdStreamController::class, 'forbidPublishing']);
    Route::put('rooms/{roomName}/participants/{identity}/resume-stream', [UtdStreamController::class, 'resumePublishing']);

    // ─── Ban Management ─────────────────────────
    Route::post('rooms/ban', [UtdStreamController::class, 'createBan']);
    Route::delete('rooms/ban', [UtdStreamController::class, 'removeBan']);
    Route::get('rooms/bans', [UtdStreamController::class, 'getBans']);

    // ─── Calls API (1-on-1) ─────────────────────
    Route::get('calls', [UtdStreamController::class, 'getCalls']);
    Route::post('calls', [UtdStreamController::class, 'makeCall']);
    Route::get('calls/{callId}', [UtdStreamController::class, 'callInfo']);
    Route::post('calls/{callId}/ringing', [UtdStreamController::class, 'ringing']);
    Route::post('calls/{callId}/accept', [UtdStreamController::class, 'accept']);
    Route::post('calls/{callId}/reject', [UtdStreamController::class, 'reject']);
    Route::post('calls/{callId}/busy', [UtdStreamController::class, 'busy']);
    Route::post('calls/{callId}/end', [UtdStreamController::class, 'end']);

    // ─── Project Info ───────────────────────────
    Route::get('project', [UtdStreamController::class, 'projectInfo']);
});
