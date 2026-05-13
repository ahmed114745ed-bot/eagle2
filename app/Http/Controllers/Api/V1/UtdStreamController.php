<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Traits\HelperTraits\UtdStreamTrait;
use Illuminate\Http\Request;

class UtdStreamController extends Controller
{
    use UtdStreamTrait;

    // ─── Token ───────────────────────────────────────────────

    public function token(Request $request)
    {
        $request->validate([
            'room_name' => 'required|string',
            'role'      => 'nullable|string|in:host,guest,audience,visitor',
            'service'   => 'required|string|in:rooms,streaming',
        ]);

        $user = $request->user();
        $identity = (string) $user->id;
        $name = $user->name ?? $user->uuid ?? $identity;

        $result = self::generateStreamToken(
            $identity,
            $request->room_name,
            $name,
            $request->role,
            $request->service
        );

        if (!$result) {
            return Common::apiResponse(false, 'Failed to generate token', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    // ─── Rooms ───────────────────────────────────────────────

    public function getRooms(Request $request)
    {
        $result = self::listRooms();

        if (!$result) {
            return Common::apiResponse(false, 'Failed to get rooms', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    public function roomInfo(Request $request, $roomName)
    {
        $result = self::getRoomInfo($roomName);

        if (!$result) {
            return Common::apiResponse(false, 'Failed to get room info', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    public function deleteRoom(Request $request, $roomName)
    {
        $result = self::closeRoom($roomName);

        if (!$result) {
            return Common::apiResponse(false, 'Failed to close room', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    public function patchRoomMetadata(Request $request, $roomName)
    {
        $request->validate([
            'metadata' => 'required',
        ]);

        $result = self::updateRoomMetadata($roomName, $request->metadata);

        if (!$result) {
            return Common::apiResponse(false, 'Failed to update room metadata', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    public function sendData(Request $request, $roomName)
    {
        $request->validate([
            'data' => 'required',
        ]);

        $destinations = $request->destination_identities;
        $result = self::sendToStream($roomName, $request->data, $destinations);

        if (!$result) {
            return Common::apiResponse(false, 'Failed to send data', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    public function kick(Request $request, $roomName, $identity)
    {
        $result = self::kickUser($roomName, $identity);

        if (!$result) {
            return Common::apiResponse(false, 'Failed to kick user', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    public function mute(Request $request, $roomName, $identity)
    {
        $result = self::muteUser(
            $roomName,
            $identity,
            $request->boolean('audio', true),
            $request->boolean('video', false)
        );

        if (!$result) {
            return Common::apiResponse(false, 'Failed to mute user', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    // ─── Participant Management ──────────────────────────────

    public function participantInfo(Request $request, $roomName, $identity)
    {
        $result = self::getParticipant($roomName, $identity);

        if (!$result) {
            return Common::apiResponse(false, 'Participant not found', null, 404);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    public function updateParticipantPermissions(Request $request, $roomName, $identity)
    {
        $request->validate([
            'can_publish' => 'nullable|boolean',
            'can_subscribe' => 'nullable|boolean',
            'can_publish_data' => 'nullable|boolean',
        ]);

        $permissions = $request->only(['can_publish', 'can_subscribe', 'can_publish_data', 'can_publish_sources', 'hidden']);

        $result = self::updatePermissions($roomName, $identity, $permissions);

        if (!$result) {
            return Common::apiResponse(false, 'Failed to update permissions', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    public function setParticipantMetadata(Request $request, $roomName, $identity)
    {
        $request->validate([
            'metadata' => 'required',
        ]);

        $result = self::updateParticipantMetadata($roomName, $identity, $request->metadata);

        if (!$result) {
            return Common::apiResponse(false, 'Failed to update metadata', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    public function forbidPublishing(Request $request, $roomName, $identity)
    {
        $result = self::forbidStream($roomName, $identity);

        if (!$result) {
            return Common::apiResponse(false, 'Failed to forbid stream', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    public function resumePublishing(Request $request, $roomName, $identity)
    {
        $result = self::resumeStream($roomName, $identity);

        if (!$result) {
            return Common::apiResponse(false, 'Failed to resume stream', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    // ─── Ban Management ──────────────────────────────────────

    public function createBan(Request $request)
    {
        $request->validate([
            'identity' => 'required|string',
            'room_name' => 'nullable|string',
            'reason' => 'nullable|string',
            'duration' => 'nullable|integer|min:1',
        ]);

        $result = self::banUser(
            $request->identity,
            $request->room_name,
            $request->reason,
            $request->duration
        );

        if (!$result) {
            return Common::apiResponse(false, 'Failed to ban user', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    public function removeBan(Request $request)
    {
        $request->validate([
            'identity' => 'required|string',
            'room_name' => 'nullable|string',
        ]);

        $result = self::unbanUser($request->identity, $request->room_name);

        if (!$result) {
            return Common::apiResponse(false, 'Failed to unban user', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    public function getBans(Request $request)
    {
        $result = self::listBans();

        if (!$result) {
            return Common::apiResponse(false, 'Failed to get bans', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    // ─── Calls ───────────────────────────────────────────────

    public function getCalls(Request $request)
    {
        $filters = $request->only(['status', 'type', 'identity', 'start_date', 'end_date', 'page', 'per_page']);

        $result = self::listCalls($filters);

        if (!$result) {
            return Common::apiResponse(false, 'Failed to get calls', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    // ─── Calls ───────────────────────────────────────────────

    public function makeCall(Request $request)
    {
        $request->validate([
            'callee_id' => 'required',
            'type'      => 'nullable|string|in:voice,video',
        ]);

        $user = $request->user();
        $callerIdentity = (string) $user->id;
        $calleeIdentity = (string) $request->callee_id;

        $result = self::initiateCall(
            $callerIdentity,
            $calleeIdentity,
            $request->type ?? 'voice',
            $request->metadata
        );

        if (!$result) {
            return Common::apiResponse(false, 'Failed to initiate call', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    public function ringing(Request $request, $callId)
    {
        $user = $request->user();
        $result = self::callRinging($callId, (string) $user->id);

        if (!$result) {
            return Common::apiResponse(false, 'Failed', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    public function accept(Request $request, $callId)
    {
        $user = $request->user();
        $result = self::acceptCall($callId, (string) $user->id);

        if (!$result) {
            return Common::apiResponse(false, 'Failed to accept call', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    public function reject(Request $request, $callId)
    {
        $user = $request->user();
        $result = self::rejectCall($callId, (string) $user->id);

        if (!$result) {
            return Common::apiResponse(false, 'Failed', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    public function busy(Request $request, $callId)
    {
        $user = $request->user();
        $result = self::callBusy($callId, (string) $user->id);

        if (!$result) {
            return Common::apiResponse(false, 'Failed', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    public function end(Request $request, $callId)
    {
        $user = $request->user();
        $result = self::endCall($callId, (string) $user->id);

        if (!$result) {
            return Common::apiResponse(false, 'Failed to end call', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    public function callInfo(Request $request, $callId)
    {
        $result = self::getCall($callId);

        if (!$result) {
            return Common::apiResponse(false, 'Call not found', null, 404);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    // ─── Project Info ────────────────────────────────────────

    public function projectInfo(Request $request)
    {
        $result = self::getProjectInfo();

        if (!$result) {
            return Common::apiResponse(false, 'Failed to get project info', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    // ─── Credential (encrypted for mobile) ───────────────────

    public function credential(Request $request)
    {
        $streamData = self::streamData();

        return Common::apiResponse(true, 'Success', [
            'app_id' => $streamData['app_id'],
        ]);
    }
}
