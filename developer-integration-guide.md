# UTD-Voice Developer Integration Guide

Welcome to UTD-Voice — a cloud communications platform (CPaaS) that gives you real-time voice and video capabilities through a simple API. Build voice calls, video calls, conferencing rooms, and more into your application in minutes.

---

## Table of Contents

1. [Getting Started](#1-getting-started)
2. [Authentication](#2-authentication)
3. [Install the Client SDK](#3-install-the-client-sdk)
4. [Quick Start — Join a Room](#4-quick-start--join-a-room)
5. [Rooms API](#5-rooms-api)
6. [Calls API (1-on-1 Voice & Video)](#6-calls-api-1-on-1-voice--video)
7. [Participant Management](#7-participant-management)
8. [Roles & Permissions](#8-roles--permissions)
9. [Webhooks (Callbacks)](#9-webhooks-callbacks)
10. [Ban Management](#10-ban-management)
11. [Project Info](#11-project-info)
12. [Rate Limits](#12-rate-limits)
13. [Error Reference](#13-error-reference)
14. [Client SDK Reference](#14-client-sdk-reference)

---

## 1. Getting Started

### Your Trial Credentials

When you sign up for a UTD-Voice trial, you will receive the following credentials:

| Credential | Format | Purpose |
|---|---|---|
| **App ID** | 10-digit number (e.g. `3847291056`) | Identifies your project |
| **App Key** | 32-character hex string | For client-side identification (not secret) |
| **Server Secret** | 32-character hex string | **Keep this secret.** Used to authenticate your server-side API calls |

> **Trial Period:** Your trial credentials are valid for the duration specified in your agreement. After the trial period expires, your project will be suspended and API calls will return `403 Forbidden`. Contact us to activate a full subscription.

> **Important:** The **Server Secret** must never be exposed in client-side code, mobile apps, or public repositories. All API calls must be made from your backend server.

### Base URL

```
https://udt-stream.com/api/v1
```

All client API endpoints are prefixed with `/api/v1`.

---

## 2. Authentication

Every API request must include two headers:

```
X-App-Id: YOUR_APP_ID
X-App-Secret: YOUR_SERVER_SECRET
```

**Example (cURL):**

```bash
curl -X POST https://udt-stream.com/api/v1/token \
  -H "Content-Type: application/json" \
  -H "X-App-Id: 3847291056" \
  -H "X-App-Secret: a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4" \
  -d '{"identity": "user-123", "room_name": "my-room"}'
```

**Example (Node.js):**

```javascript
const response = await fetch('https://udt-stream.com/api/v1/token', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-App-Id': process.env.UTD_APP_ID,
    'X-App-Secret': process.env.UTD_SERVER_SECRET,
  },
  body: JSON.stringify({
    identity: 'user-123',
    room_name: 'my-room',
  }),
});

const { token, url } = await response.json();
```

---

## 3. Install the Client SDK

UTD-Voice uses the **LiveKit** protocol. You need to install the LiveKit Client SDK for your platform to connect users to rooms and calls.

### Web (JavaScript / TypeScript)

```bash
npm install livekit-client
```

- GitHub: https://github.com/livekit/client-sdk-js
- Docs: https://docs.livekit.io/client-sdk-js/

### React

```bash
npm install @livekit/components-react livekit-client
```

- GitHub: https://github.com/livekit/components-js
- Docs: https://docs.livekit.io/reference/components/react/

### React Native

```bash
npm install @livekit/react-native livekit-client
```

- GitHub: https://github.com/livekit/client-sdk-react-native
- Docs: https://docs.livekit.io/client-sdk-rn/

### iOS (Swift)

```swift
// Swift Package Manager
// Add: https://github.com/livekit/client-sdk-swift
```

- GitHub: https://github.com/livekit/client-sdk-swift
- Docs: https://docs.livekit.io/client-sdk-swift/

### Android (Kotlin)

```kotlin
// build.gradle
implementation 'io.livekit:livekit-android:2.+'
```

- GitHub: https://github.com/livekit/client-sdk-android
- Docs: https://docs.livekit.io/client-sdk-android/

### Flutter

```bash
flutter pub add livekit_client
```

- GitHub: https://github.com/livekit/client-sdk-flutter
- Docs: https://docs.livekit.io/client-sdk-flutter/

### Unity

- GitHub: https://github.com/livekit/client-sdk-unity

### Full SDK Documentation

For detailed SDK documentation, examples, and guides:
- https://docs.livekit.io/realtime/

---

## 4. Quick Start — Join a Room

This is the simplest flow: your backend requests a token, your frontend uses it to join a room.

### Step 1: Request a Token (Backend)

```javascript
// your-backend/routes/voice.js
app.post('/get-voice-token', async (req, res) => {
  const { userId, userName, roomName } = req.body;

  const response = await fetch('https://udt-stream.com/api/v1/token', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-App-Id': process.env.UTD_APP_ID,
      'X-App-Secret': process.env.UTD_SERVER_SECRET,
    },
    body: JSON.stringify({
      identity: userId,
      name: userName,
      room_name: roomName,
    }),
  });

  const data = await response.json();
  // data = { token: "eyJ...", url: "wss://...", room_name: "my-room" }

  res.json(data);
});
```

### Step 2: Connect to the Room (Frontend — JavaScript)

```javascript
import { Room, RoomEvent } from 'livekit-client';

// Get token from your backend
const { token, url } = await fetch('/get-voice-token', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    userId: 'user-123',
    userName: 'Ahmed',
    roomName: 'team-meeting',
  }),
}).then(r => r.json());

// Create and connect to the room
const room = new Room();

room.on(RoomEvent.ParticipantConnected, (participant) => {
  console.log('Participant joined:', participant.identity);
});

room.on(RoomEvent.TrackSubscribed, (track, publication, participant) => {
  if (track.kind === 'audio') {
    const audio = track.attach();
    document.body.appendChild(audio);
  }
});

room.on(RoomEvent.Disconnected, () => {
  console.log('Disconnected from room');
});

await room.connect(url, token);

// Publish your microphone
await room.localParticipant.setMicrophoneEnabled(true);

console.log('Connected to room:', room.name);
```

### Step 2 (Alternative): Connect with React

```jsx
import { LiveKitRoom, AudioConference } from '@livekit/components-react';
import '@livekit/components-styles';

function VoiceRoom({ token, url }) {
  return (
    <LiveKitRoom serverUrl={url} token={token} connect={true}>
      <AudioConference />
    </LiveKitRoom>
  );
}
```

---

## 5. Rooms API

### Generate Token

Creates a room (if it doesn't exist) and returns a connection token.

```
POST /api/v1/token
```

**Request Body:**

| Field | Type | Required | Description |
|---|---|---|---|
| `identity` | string | Yes | Unique user identifier within the room |
| `name` | string | No | Display name for the participant |
| `room_name` | string | Yes | Room identifier (you define this) |
| `role` | string | No | One of: `host`, `guest`, `audience`, `visitor` |
| `metadata` | object | No | Custom data attached to the participant |
| `permissions` | object | No | Override permissions (ignored if `role` is set) |

**Response (200):**

```json
{
  "token": "eyJhbGciOiJIUzI1NiIs...",
  "url": "wss://livekit.udt-stream.com",
  "room_name": "my-room"
}
```

### List Active Rooms

```
GET /api/v1/rooms
```

**Response (200):**

```json
{
  "rooms": [
    {
      "id": 1,
      "room_name": "team-meeting",
      "participant_count": 5,
      "max_participants": 50,
      "started_at": "2026-05-10T10:30:00.000Z",
      "metadata": null
    }
  ]
}
```

### Get Room Details

```
GET /api/v1/rooms/:room_name
```

**Response (200):**

```json
{
  "id": 1,
  "room_name": "team-meeting",
  "participant_count": 5,
  "max_participants": 50,
  "started_at": "2026-05-10T10:30:00.000Z",
  "metadata": null,
  "participants": [
    {
      "identity": "user-123",
      "name": "Ahmed",
      "state": "ACTIVE",
      "joined_at": 1715340600,
      "tracks": 2
    }
  ]
}
```

### Close a Room

Removes all participants and closes the room.

```
DELETE /api/v1/rooms/:room_name
```

**Response (200):**

```json
{ "message": "Room closed" }
```

### Update Room Metadata

```
PUT /api/v1/rooms/:room_name/metadata
```

**Request Body:**

```json
{ "metadata": { "topic": "Sprint Planning", "recording": true } }
```

### Send Data to Room

Send arbitrary data to all participants or specific ones.

```
POST /api/v1/rooms/:room_name/send-data
```

**Request Body:**

```json
{
  "data": { "type": "chat", "message": "Hello everyone!" },
  "destination_identities": ["user-456"]
}
```

`destination_identities` is optional — omit it to broadcast to all participants.

---

## 6. Calls API (1-on-1 Voice & Video)

The Calls API handles direct 1-on-1 calls with a full lifecycle: initiate, ring, accept/reject, and end.

> **Note:** Calls must be enabled on your project. If you get `403 Calls are not enabled`, contact us to enable the feature.

### Call Flow

```
Caller                          UTD-Voice                        Callee
  |                                |                               |
  |-- POST /calls --------------->|                               |
  |<-- 201 { call_id, token } ----|                               |
  |   (caller connects to room)   |                               |
  |                                |                               |
  |                                |-- webhook: call_initiated --->|
  |                                |                               |
  |                                |<-- POST /calls/:id/ringing ---|
  |                                |-- webhook: call_ringing ----->|
  |                                |                               |
  |                                |<-- POST /calls/:id/accept ----|
  |                                |-- 200 { token } ------------->|
  |                                |   (callee connects to room)   |
  |                                |                               |
  |   ... call in progress ...     |                               |
  |                                |                               |
  |-- POST /calls/:id/end ------->|                               |
  |<-- 200 { duration } ----------|                               |
```

### Initiate a Call

```
POST /api/v1/calls
```

**Request Body:**

| Field | Type | Required | Description |
|---|---|---|---|
| `caller_identity` | string | Yes | Caller's unique identifier |
| `callee_identity` | string | Yes | Callee's unique identifier |
| `caller_name` | string | No | Caller's display name |
| `callee_name` | string | No | Callee's display name |
| `type` | string | No | `voice` (default) or `video` |
| `metadata` | object | No | Custom data attached to the call |

**Response (201):**

```json
{
  "call_id": "call_a1b2c3d4e5f6a1b2c3d4e5f6",
  "status": "initiating",
  "room_name": "call_a1b2c3d4e5f6a1b2c3d4e5f6",
  "token": "eyJhbGciOiJIUzI1NiIs...",
  "url": "wss://livekit.udt-stream.com",
  "type": "voice",
  "callee_identity": "user-456"
}
```

**Error Cases:**

| Status | Message | Cause |
|---|---|---|
| 409 | `Callee is already on a call` | Callee has an active/ringing call |
| 403 | `Calls are not enabled for this project` | Feature not enabled |
| 403 | `Video is not enabled for this project` | Video calls require video to be enabled |

### Callee: Mark as Ringing

Call this when the callee's device starts ringing (e.g., push notification received).

```
POST /api/v1/calls/:call_id/ringing
```

```json
{ "identity": "user-456" }
```

### Callee: Accept the Call

```
POST /api/v1/calls/:call_id/accept
```

```json
{ "identity": "user-456" }
```

**Response (200):**

```json
{
  "call_id": "call_a1b2c3d4e5f6...",
  "status": "active",
  "token": "eyJ...",
  "url": "wss://livekit.udt-stream.com",
  "room_name": "call_a1b2c3d4e5f6..."
}
```

The callee uses the returned `token` and `url` to connect to the room via the LiveKit SDK.

### Callee: Reject the Call

```
POST /api/v1/calls/:call_id/reject
```

```json
{ "identity": "user-456" }
```

### Callee: Busy

Use when the callee is already occupied and cannot take the call.

```
POST /api/v1/calls/:call_id/busy
```

```json
{ "identity": "user-456" }
```

### End a Call

Either the caller or callee can end an active call.

```
POST /api/v1/calls/:call_id/end
```

```json
{ "identity": "user-123" }
```

**Response (200):**

```json
{
  "call_id": "call_a1b2c3d4e5f6...",
  "status": "ended",
  "duration_seconds": 125,
  "end_reason": "caller_hangup"
}
```

### Get Call Details

```
GET /api/v1/calls/:call_id
```

### List Calls

```
GET /api/v1/calls?status=active&type=voice&identity=user-123&page=1&per_page=20
```

All query parameters are optional. Supports filters: `status`, `type`, `identity`, `start_date`, `end_date`.

### Call States

| State | Description |
|---|---|
| `initiating` | Call created, waiting for callee's device |
| `ringing` | Callee's device is ringing |
| `active` | Call is connected and in progress |
| `ended` | Call completed normally |
| `rejected` | Callee declined the call |
| `missed` | Callee didn't answer within the timeout |
| `busy` | Callee was busy |

### Automatic Timeout

If the callee doesn't answer within the configured ring timeout (default 30 seconds), the call is automatically marked as `missed` and the room is cleaned up. You will receive a `call_missed` webhook event.

### Full Call Integration Example

```javascript
// === BACKEND (your server) ===

// Initiate a call
app.post('/call', async (req, res) => {
  const { callerId, calleeId, type } = req.body;

  const response = await fetch('https://udt-stream.com/api/v1/calls', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-App-Id': process.env.UTD_APP_ID,
      'X-App-Secret': process.env.UTD_SERVER_SECRET,
    },
    body: JSON.stringify({
      caller_identity: callerId,
      callee_identity: calleeId,
      type: type || 'voice',
    }),
  });

  const data = await response.json();
  // Send push notification to callee with call_id
  await sendPushNotification(calleeId, {
    type: 'incoming_call',
    call_id: data.call_id,
    caller: callerId,
    call_type: type,
  });

  res.json(data);
});

// Accept a call (called by callee's device)
app.post('/call/:callId/accept', async (req, res) => {
  const { calleeId } = req.body;

  // Mark as ringing first
  await fetch(`https://udt-stream.com/api/v1/calls/${req.params.callId}/ringing`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-App-Id': process.env.UTD_APP_ID,
      'X-App-Secret': process.env.UTD_SERVER_SECRET,
    },
    body: JSON.stringify({ identity: calleeId }),
  });

  // Then accept
  const response = await fetch(`https://udt-stream.com/api/v1/calls/${req.params.callId}/accept`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-App-Id': process.env.UTD_APP_ID,
      'X-App-Secret': process.env.UTD_SERVER_SECRET,
    },
    body: JSON.stringify({ identity: calleeId }),
  });

  const data = await response.json();
  // data.token and data.url are used by the callee to connect
  res.json(data);
});


// === FRONTEND (caller side) ===

import { Room } from 'livekit-client';

async function makeCall(calleeId, type = 'voice') {
  // 1. Initiate call via your backend
  const { call_id, token, url } = await fetch('/call', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ callerId: myUserId, calleeId, type }),
  }).then(r => r.json());

  // 2. Connect to the call room
  const room = new Room();

  room.on('participantConnected', () => {
    console.log('Callee connected — call is active');
  });

  room.on('disconnected', () => {
    console.log('Call ended');
  });

  await room.connect(url, token);
  await room.localParticipant.setMicrophoneEnabled(true);

  if (type === 'video') {
    await room.localParticipant.setCameraEnabled(true);
  }

  return { room, call_id };
}

async function endCall(callId) {
  await fetch(`/call/${callId}/end`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ identity: myUserId }),
  });
}
```

---

## 7. Participant Management

### Get Participant Info

```
GET /api/v1/rooms/:room_name/participants/:identity
```

**Response (200):**

```json
{
  "identity": "user-123",
  "name": "Ahmed",
  "state": "ACTIVE",
  "joined_at": 1715340600,
  "metadata": "{\"role\":\"host\"}",
  "permission": {
    "canPublish": true,
    "canSubscribe": true,
    "canPublishData": true
  },
  "tracks": [
    { "sid": "TR_abc123", "type": "AUDIO", "muted": false },
    { "sid": "TR_def456", "type": "VIDEO", "muted": false, "width": 1280, "height": 720 }
  ]
}
```

### Remove Participant

```
DELETE /api/v1/rooms/:room_name/participants/:identity
```

### Update Participant Permissions

```
PUT /api/v1/rooms/:room_name/participants/:identity/permissions
```

```json
{
  "can_publish": true,
  "can_subscribe": true,
  "can_publish_data": false
}
```

### Mute Participant

```
PUT /api/v1/rooms/:room_name/participants/:identity/mute
```

```json
{ "audio": true, "video": false }
```

Or mute a specific track:

```json
{ "track_sid": "TR_abc123", "muted": true }
```

### Update Participant Metadata

```
PUT /api/v1/rooms/:room_name/participants/:identity/metadata
```

```json
{ "metadata": { "hand_raised": true } }
```

### Forbid / Resume Publishing

Temporarily prevent a participant from publishing:

```
PUT /api/v1/rooms/:room_name/participants/:identity/forbid-stream
PUT /api/v1/rooms/:room_name/participants/:identity/resume-stream
```

---

## 8. Roles & Permissions

When generating a token, you can assign a `role` to define what the participant can do:

| Role | Publish Audio | Publish Video | Screen Share | Subscribe | Send Data |
|---|---|---|---|---|---|
| `host` | Yes | Yes | Yes | Yes | Yes |
| `guest` | Yes | Yes | No | Yes | Yes |
| `audience` | No | No | No | Yes | Yes |
| `visitor` | No | No | No | Yes | No |

**Usage:**

```json
{
  "identity": "user-123",
  "room_name": "webinar",
  "role": "audience"
}
```

If you need custom permissions, use the `permissions` object instead of `role`:

```json
{
  "identity": "user-123",
  "room_name": "webinar",
  "permissions": {
    "canPublish": true,
    "canSubscribe": true,
    "canPublishData": false
  }
}
```

---

## 9. Webhooks (Callbacks)

UTD-Voice can send real-time event notifications to your server via HTTP POST webhooks.

> **Setup:** Provide your `callback_url` when your project is configured. You'll also receive a `callback_secret` for signature verification.

### Events

**Room Events:**

| Event | Trigger |
|---|---|
| `room_started` | A new room is created |
| `room_finished` | A room is closed (empty or deleted) |
| `participant_joined` | A participant connects to a room |
| `participant_left` | A participant disconnects from a room |
| `track_published` | A participant starts publishing audio/video |
| `track_unpublished` | A participant stops publishing |

**Call Events:**

| Event | Trigger |
|---|---|
| `call_initiated` | A new call is created |
| `call_ringing` | Callee's device is ringing |
| `call_accepted` | Callee accepted the call |
| `call_rejected` | Callee rejected the call |
| `call_busy` | Callee was busy |
| `call_ended` | Call ended (by either party or system) |
| `call_missed` | Callee didn't answer (timeout) |

### Webhook Payload

```json
{
  "event": "participant_joined",
  "room": {
    "room_name": "team-meeting",
    "participant_count": 5
  },
  "participant": {
    "identity": "user-123",
    "name": "Ahmed"
  }
}
```

### Webhook Headers

| Header | Description |
|---|---|
| `X-UTD-Voice-Event` | Event name (e.g. `participant_joined`) |
| `X-UTD-Voice-Signature` | HMAC-SHA256 signature: `sha256={hex}` |
| `X-UTD-Voice-Attempt` | Attempt number (1-6) |

### Verifying the Signature

Always verify webhook signatures to ensure the request is from UTD-Voice:

```javascript
import crypto from 'node:crypto';

function verifyWebhook(req, callbackSecret) {
  const signature = req.headers['x-utd-voice-signature'];
  if (!signature) return false;

  const expected = 'sha256=' + crypto
    .createHmac('sha256', callbackSecret)
    .update(JSON.stringify(req.body))
    .digest('hex');

  return crypto.timingSafeEqual(
    Buffer.from(signature),
    Buffer.from(expected)
  );
}

// Express middleware
app.post('/webhooks/utd-voice', express.json(), (req, res) => {
  if (!verifyWebhook(req, process.env.UTD_CALLBACK_SECRET)) {
    return res.status(401).send('Invalid signature');
  }

  const { event } = req.body;

  switch (event) {
    case 'participant_joined':
      console.log('User joined:', req.body.participant.identity);
      break;
    case 'call_initiated':
      // Send push notification to callee
      break;
    case 'call_missed':
      // Show missed call notification
      break;
  }

  res.status(200).send('OK');
});
```

### Retry Policy

If your server doesn't respond with a `2xx` status within **5 seconds**, UTD-Voice will retry with exponential backoff:

| Attempt | Delay |
|---|---|
| 1 | Immediate |
| 2 | 2 seconds |
| 3 | 4 seconds |
| 4 | 8 seconds |
| 5 | 16 seconds |
| 6 | 32 seconds |

After 6 failed attempts, the event is dropped. Client errors (4xx) are not retried.

---

## 10. Ban Management

Ban users from specific rooms or your entire project.

### Ban a User

```
POST /api/v1/rooms/ban
```

```json
{
  "identity": "user-789",
  "room_name": "public-chat",
  "reason": "Violation of community guidelines",
  "duration": 3600
}
```

| Field | Type | Required | Description |
|---|---|---|---|
| `identity` | string | Yes | User identity to ban |
| `room_name` | string | No | Specific room (omit for project-wide ban) |
| `reason` | string | No | Reason for the ban |
| `duration` | number | No | Ban duration in seconds (omit for permanent) |

### Unban a User

```
DELETE /api/v1/rooms/ban
```

```json
{
  "identity": "user-789",
  "room_name": "public-chat"
}
```

### List All Bans

```
GET /api/v1/rooms/bans
```

### How Bans Work

When a banned user tries to generate a token:
- If they have a **project-wide ban** → `403 User is banned` (for any room)
- If they have a **room-specific ban** → `403 User is banned` (only for that room)
- Expired bans are automatically ignored

---

## 11. Project Info

Get your project's current configuration and limits:

```
GET /api/v1/project
```

**Response (200):**

```json
{
  "name": "My App",
  "app_id": "3847291056",
  "status": "active",
  "region": "me-central1",
  "max_concurrent_rooms": 100,
  "max_participants_per_room": 50,
  "audio_enabled": true,
  "video_enabled": false,
  "max_video_quality": "hd",
  "empty_room_timeout": 300,
  "calls_enabled": true,
  "call_ring_timeout": 30,
  "max_call_duration": 3600,
  "max_call_video_quality": "hd",
  "active_rooms": 3
}
```

---

## 12. Rate Limits

| Limit | Value |
|---|---|
| Requests per minute | 120 |
| Max rooms per project | Configurable (default 100) |
| Max participants per room | Configurable (default 50) |
| Token TTL | 1 hour |

When you exceed the rate limit, you'll receive:

```
HTTP 429 Too Many Requests
```

---

## 13. Error Reference

### HTTP Status Codes

| Status | Meaning |
|---|---|
| `200` | Success |
| `201` | Created (new call, new ban) |
| `401` | Missing or invalid credentials |
| `403` | Project suspended, user banned, or feature disabled |
| `404` | Room, participant, or call not found |
| `409` | Conflict (e.g., callee already on a call, invalid state transition) |
| `422` | Missing required fields or invalid values |
| `429` | Rate limit exceeded |
| `500` | Internal server error |

### Common Error Responses

```json
{ "message": "Missing X-App-Id or X-App-Secret header" }
{ "message": "Invalid credentials" }
{ "message": "Project is suspended" }
{ "message": "Client account is suspended" }
{ "message": "User is banned" }
{ "message": "Maximum concurrent rooms reached" }
{ "message": "Calls are not enabled for this project" }
{ "message": "Callee is already on a call" }
{ "message": "identity and room_name are required" }
{ "message": "caller_identity and callee_identity are required" }
{ "message": "Only the callee can accept the call" }
```

---

## 14. Client SDK Reference

### LiveKit Documentation

- **Getting Started:** https://docs.livekit.io/realtime/
- **JavaScript SDK:** https://docs.livekit.io/client-sdk-js/
- **React Components:** https://docs.livekit.io/reference/components/react/
- **iOS SDK:** https://docs.livekit.io/client-sdk-swift/
- **Android SDK:** https://docs.livekit.io/client-sdk-android/
- **Flutter SDK:** https://docs.livekit.io/client-sdk-flutter/
- **React Native:** https://docs.livekit.io/client-sdk-rn/

### GitHub Repositories

| Platform | Repository |
|---|---|
| JavaScript | https://github.com/livekit/client-sdk-js |
| React | https://github.com/livekit/components-js |
| iOS (Swift) | https://github.com/livekit/client-sdk-swift |
| Android (Kotlin) | https://github.com/livekit/client-sdk-android |
| Flutter | https://github.com/livekit/client-sdk-flutter |
| React Native | https://github.com/livekit/client-sdk-react-native |
| Unity | https://github.com/livekit/client-sdk-unity |

---

## Need Help?

- **API Status:** `GET /health` — returns `{ "status": "ok" }` if the platform is running
- **Contact:** Reach out to the UTD-Voice team for support, feature requests, or to upgrade from your trial
