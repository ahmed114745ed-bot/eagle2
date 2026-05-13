<?php

namespace App\Traits\HelperTraits;

use App\Helpers\Common;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait UtdStreamTrait
{
    private static $streamBaseUrl = 'https://udt-stream.com/api/v1';

    public static function streamData($key = null)
    {
        $data = [
            'app_id' => Common::getConfig('utd_stream_app_id') ?? '',
            'server_secret' => Common::getConfig('utd_stream_server_secret') ?? '',
        ];

        if ($key) {
            return $data[$key] ?? null;
        }

        return $data;
    }

    private static function streamRequest($method, $path, $data = [])
    {
        $appId = self::streamData('app_id');
        $secret = self::streamData('server_secret');

        $headers = [
            'X-App-Id' => $appId,
            'X-App-Secret' => $secret,
        ];

        $url = self::$streamBaseUrl . $path;

        try {
            $request = Http::withHeaders($headers)->acceptJson()->timeout(20);

            $response = match (strtoupper($method)) {
                'GET' => $request->get($url, $data),
                'POST' => $request->post($url, $data),
                'PUT' => $request->put($url, $data),
                'DELETE' => $request->delete($url, $data),
                default => $request->post($url, $data),
            };

            return $response->json();
        } catch (\Exception $e) {
            Log::error('UTD-STREAM API error', [
                'method' => $method,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    // ─── Token ───────────────────────────────────────────────

    public static function generateStreamToken($identity, $roomName, $name = null, $role = null, $service = null)
    {
        $body = [
            'identity' => $identity,
            'room_name' => $roomName,
        ];

        if ($name) $body['name'] = $name;
        if ($role) $body['role'] = $role;
        if ($service) $body['service'] = $service;

        return self::streamRequest('POST', '/token', $body);
    }

    // ─── Rooms ───────────────────────────────────────────────

    public static function listRooms()
    {
        return self::streamRequest('GET', '/rooms');
    }

    public static function getRoomInfo($roomName)
    {
        return self::streamRequest('GET', '/rooms/' . urlencode($roomName));
    }

    public static function closeRoom($roomName)
    {
        return self::streamRequest('DELETE', '/rooms/' . urlencode($roomName));
    }

    public static function updateRoomMetadata($roomName, $metadata)
    {
        return self::streamRequest('PUT', '/rooms/' . urlencode($roomName) . '/metadata', [
            'metadata' => $metadata,
        ]);
    }

    // ─── Send Data (replaces sendToZego) ─────────────────────

    public static function sendToStream($roomName, $data, $destinationIdentities = null)
    {
        $body = ['data' => $data];

        if ($destinationIdentities) {
            $body['destination_identities'] = (array)$destinationIdentities;
        }

        return self::streamRequest('POST', '/rooms/' . urlencode($roomName) . '/send-data', $body);
    }

    public static function sendToStreamUser($roomName, $toIdentity, $data)
    {
        return self::sendToStream($roomName, $data, [$toIdentity]);
    }

    // ─── Participants ────────────────────────────────────────

    public static function getParticipant($roomName, $identity)
    {
        return self::streamRequest('GET', '/rooms/' . urlencode($roomName) . '/participants/' . urlencode($identity));
    }

    public static function kickUser($roomName, $identity)
    {
        return self::streamRequest('DELETE', '/rooms/' . urlencode($roomName) . '/participants/' . urlencode($identity));
    }

    public static function muteUser($roomName, $identity, $audio = true, $video = false)
    {
        return self::streamRequest('PUT', '/rooms/' . urlencode($roomName) . '/participants/' . urlencode($identity) . '/mute', [
            'audio' => $audio,
            'video' => $video,
        ]);
    }

    public static function updatePermissions($roomName, $identity, $permissions)
    {
        return self::streamRequest('PUT', '/rooms/' . urlencode($roomName) . '/participants/' . urlencode($identity) . '/permissions', $permissions);
    }

    public static function updateParticipantMetadata($roomName, $identity, $metadata)
    {
        return self::streamRequest('PUT', '/rooms/' . urlencode($roomName) . '/participants/' . urlencode($identity) . '/metadata', [
            'metadata' => $metadata,
        ]);
    }

    public static function forbidStream($roomName, $identity)
    {
        return self::streamRequest('PUT', '/rooms/' . urlencode($roomName) . '/participants/' . urlencode($identity) . '/forbid-stream');
    }

    public static function resumeStream($roomName, $identity)
    {
        return self::streamRequest('PUT', '/rooms/' . urlencode($roomName) . '/participants/' . urlencode($identity) . '/resume-stream');
    }

    // ─── Calls ───────────────────────────────────────────────

    public static function initiateCall($callerIdentity, $calleeIdentity, $type = 'voice', $metadata = null)
    {
        $body = [
            'caller_identity' => $callerIdentity,
            'callee_identity' => $calleeIdentity,
            'type' => $type,
        ];

        if ($metadata) $body['metadata'] = $metadata;

        return self::streamRequest('POST', '/calls', $body);
    }

    public static function callRinging($callId, $identity)
    {
        return self::streamRequest('POST', '/calls/' . urlencode($callId) . '/ringing', [
            'identity' => $identity,
        ]);
    }

    public static function acceptCall($callId, $identity)
    {
        return self::streamRequest('POST', '/calls/' . urlencode($callId) . '/accept', [
            'identity' => $identity,
        ]);
    }

    public static function rejectCall($callId, $identity)
    {
        return self::streamRequest('POST', '/calls/' . urlencode($callId) . '/reject', [
            'identity' => $identity,
        ]);
    }

    public static function callBusy($callId, $identity)
    {
        return self::streamRequest('POST', '/calls/' . urlencode($callId) . '/busy', [
            'identity' => $identity,
        ]);
    }

    public static function endCall($callId, $identity)
    {
        return self::streamRequest('POST', '/calls/' . urlencode($callId) . '/end', [
            'identity' => $identity,
        ]);
    }

    public static function getCall($callId)
    {
        return self::streamRequest('GET', '/calls/' . urlencode($callId));
    }

    public static function listCalls($filters = [])
    {
        return self::streamRequest('GET', '/calls', $filters);
    }

    // ─── Bans ────────────────────────────────────────────────

    public static function banUser($identity, $roomName = null, $reason = null, $duration = null)
    {
        $body = ['identity' => $identity];

        if ($roomName) $body['room_name'] = $roomName;
        if ($reason) $body['reason'] = $reason;
        if ($duration) $body['duration'] = $duration;

        return self::streamRequest('POST', '/rooms/ban', $body);
    }

    public static function unbanUser($identity, $roomName = null)
    {
        $body = ['identity' => $identity];

        if ($roomName) $body['room_name'] = $roomName;

        return self::streamRequest('DELETE', '/rooms/ban', $body);
    }

    public static function listBans()
    {
        return self::streamRequest('GET', '/rooms/bans');
    }

    // ─── Project Info ────────────────────────────────────────

    public static function getProjectInfo()
    {
        return self::streamRequest('GET', '/project');
    }
}
