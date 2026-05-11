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
        ]);

        $user = $request->user();
        $identity = (string) $user->id;
        $name = $user->name ?? $user->uuid ?? $identity;

        $result = self::generateStreamToken(
            $identity,
            $request->room_name,
            $name,
            $request->role
        );

        if (!$result) {
            return Common::apiResponse(false, 'Failed to generate token', null, 500);
        }

        return Common::apiResponse(true, 'Success', $result);
    }

    // ─── Rooms ───────────────────────────────────────────────

    public function roomInfo(Request $request, $roomName)
    {
        $result = self::getRoomInfo($roomName);

        if (!$result) {
            return Common::apiResponse(false, 'Failed to get room info', null, 500);
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

    // ─── Credential (encrypted for mobile) ───────────────────

    public function credential(Request $request)
    {
        $streamData = self::streamData();

        return Common::apiResponse(true, 'Success', [
            'app_id' => $streamData['app_id'],
        ]);
    }
}
