# UTD-Voice Dashboard Developer Guide

This guide covers everything you need to build the UTD-Voice admin dashboard — the control panel where clients sign up, create projects, get their credentials, and monitor their usage.

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [Authentication](#2-authentication)
3. [Client Management](#3-client-management)
4. [Project Management](#4-project-management)
5. [Trial Period Management](#5-trial-period-management)
6. [Dashboard Statistics](#6-dashboard-statistics)
7. [Usage Analytics](#7-usage-analytics)
8. [Live Room Monitoring](#8-live-room-monitoring)
9. [Call Monitoring & Statistics](#9-call-monitoring--statistics)
10. [API Logs](#10-api-logs)
11. [Pagination](#11-pagination)
12. [Error Handling](#12-error-handling)
13. [Suggested Dashboard Pages](#13-suggested-dashboard-pages)

---

## 1. Architecture Overview

```
┌─────────────────────────────────────────────────────┐
│                   Admin Dashboard                    │
│              (React / Vue / Next.js)                 │
└──────────────────────┬──────────────────────────────┘
                       │ HTTP requests with X-Admin-Key
                       ▼
┌─────────────────────────────────────────────────────┐
│              UTD-Voice Backend API                   │
│                   /admin/*                           │
├─────────────────────────────────────────────────────┤
│  /admin/clients          → Client CRUD              │
│  /admin/clients/:id/projects → Create project       │
│  /admin/projects/:id     → Project CRUD             │
│  /admin/dashboard        → Real-time stats          │
│  /admin/usage            → Usage analytics          │
│  /admin/rooms            → Live room monitoring     │
│  /admin/calls            → Call monitoring          │
│  /admin/logs             → API access logs          │
└─────────────────────────────────────────────────────┘
```

**Base URL:** `https://udt-stream.com`

All admin endpoints are prefixed with `/admin`.

### Key Concepts

| Concept | Description |
|---|---|
| **Client** | A customer/company who uses UTD-Voice. Has an `api_key` (uvk_...) for identification |
| **Project** | An application belonging to a client. Has `app_id`, `app_key`, and `server_secret` credentials. A client can have multiple projects |
| **Room** | A real-time audio/video session created by a project |
| **Call** | A 1-on-1 voice or video call within a project |

### Data Relationships

```
Client (1) ──── (N) Project (1) ──── (N) Room
                                 └──── (N) Call
                                 └──── (N) Usage Daily
                                 └──── (N) API Log
```

---

## 2. Authentication

All admin API requests require the `X-Admin-Key` header:

```
X-Admin-Key: YOUR_ADMIN_KEY
```

The admin key is a shared secret configured on the server (environment variable `ADMIN_KEY`).

### Example Request

```javascript
const ADMIN_API = 'https://udt-stream.com/admin';
const ADMIN_KEY = process.env.ADMIN_KEY;

async function adminFetch(path, options = {}) {
  const response = await fetch(`${ADMIN_API}${path}`, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      'X-Admin-Key': ADMIN_KEY,
      ...options.headers,
    },
    body: options.body ? JSON.stringify(options.body) : undefined,
  });

  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message);
  }

  return response.json();
}
```

### Error Responses

| Status | Meaning |
|---|---|
| `401` | Missing or invalid `X-Admin-Key` |

```json
{ "message": "Missing X-Admin-Key header" }
{ "message": "Invalid admin key" }
```

---

## 3. Client Management

### Create a Client

```
POST /admin/clients
```

**Request Body:**

| Field | Type | Required | Description |
|---|---|---|---|
| `name` | string | Yes | Client's full name or company name |
| `email` | string | Yes | Unique email address |
| `phone` | string | No | Phone number |
| `company_name` | string | No | Company name |

**Response (201):**

```json
{
  "id": 1,
  "name": "Acme Corp",
  "email": "admin@acme.com",
  "phone": "+966501234567",
  "company_name": "Acme Corporation",
  "api_key": "uvk_a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4",
  "status": "active",
  "created_at": "2026-05-10T12:00:00.000Z",
  "updated_at": "2026-05-10T12:00:00.000Z"
}
```

> **Note:** The `api_key` (uvk_...) is auto-generated and returned only at creation. Store it — it identifies this client.

**Error Cases:**

| Status | Cause |
|---|---|
| 422 | `name and email are required` |
| 409 | `Email already registered` |

### List Clients

```
GET /admin/clients?page=1&per_page=20&status=active&search=acme
```

| Parameter | Type | Description |
|---|---|---|
| `page` | number | Page number (default: 1) |
| `per_page` | number | Items per page (default: 20, max: 100) |
| `status` | string | Filter by `active` or `suspended` |
| `search` | string | Search in name, email, or company_name |

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "name": "Acme Corp",
      "email": "admin@acme.com",
      "phone": "+966501234567",
      "company_name": "Acme Corporation",
      "api_key": "uvk_...",
      "status": "active",
      "created_at": "2026-05-10T12:00:00.000Z",
      "updated_at": "2026-05-10T12:00:00.000Z"
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 1,
    "total_pages": 1
  }
}
```

### Get Client Details

Returns the client with all their projects.

```
GET /admin/clients/:id
```

**Response (200):**

```json
{
  "id": 1,
  "name": "Acme Corp",
  "email": "admin@acme.com",
  "phone": "+966501234567",
  "company_name": "Acme Corporation",
  "api_key": "uvk_...",
  "status": "active",
  "created_at": "2026-05-10T12:00:00.000Z",
  "updated_at": "2026-05-10T12:00:00.000Z",
  "projects": [
    {
      "id": 1,
      "name": "My App",
      "app_id": "3847291056",
      "status": "active",
      "created_at": "2026-05-10T12:30:00.000Z"
    }
  ]
}
```

### Update Client

```
PUT /admin/clients/:id
```

**Request Body (all optional):**

```json
{
  "name": "Acme International",
  "email": "new@acme.com",
  "phone": "+966509876543",
  "company_name": "Acme International Inc."
}
```

### Suspend / Activate Client

```
PATCH /admin/clients/:id/status
```

```json
{ "status": "suspended" }
```

> **Important:** Suspending a client automatically suspends **all** their projects. Their API calls will return `403 Client account is suspended`.

```json
{ "status": "active" }
```

> **Note:** Activating a client does NOT automatically reactivate their projects. You must reactivate each project individually.

---

## 4. Project Management

### Create a Project

```
POST /admin/clients/:clientId/projects
```

**Request Body:**

| Field | Type | Required | Default | Description |
|---|---|---|---|---|
| `name` | string | Yes | — | Project name |
| `callback_url` | string | No | null | Webhook URL for events |
| `allowed_origins` | string[] | No | null | CORS allowed origins |
| `max_concurrent_rooms` | number | No | 100 | Max active rooms |
| `max_participants_per_room` | number | No | 50 | Max users per room |
| `empty_room_timeout` | number | No | 300 | Seconds before empty room closes |
| `audio_enabled` | boolean | No | true | Allow audio publishing |
| `video_enabled` | boolean | No | false | Allow video publishing |
| `max_video_quality` | string | No | "hd" | sd, hd, fhd, 2k, 4k |
| `calls_enabled` | boolean | No | false | Enable 1-on-1 calls |
| `call_ring_timeout` | number | No | 30 | Ring timeout seconds (10-120) |
| `max_call_duration` | number | No | 3600 | Max call length seconds (60-14400) |
| `max_call_video_quality` | string | No | null | sd, hd, fhd, 2k, 4k |
| `settings` | object | No | null | Custom settings (e.g., rate limit override) |

**Response (201):**

```json
{
  "id": 1,
  "client_id": 1,
  "name": "My App",
  "app_id": "3847291056",
  "app_key": "a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4",
  "server_secret": "f6e5d4c3b2a1f6e5d4c3b2a1f6e5d4c3",
  "callback_url": null,
  "callback_secret": null,
  "allowed_origins": null,
  "max_concurrent_rooms": 100,
  "max_participants_per_room": 50,
  "empty_room_timeout": 300,
  "audio_enabled": true,
  "video_enabled": false,
  "max_video_quality": "hd",
  "calls_enabled": false,
  "call_ring_timeout": 30,
  "max_call_duration": 3600,
  "max_call_video_quality": null,
  "region": "me-central1",
  "settings": null,
  "status": "active",
  "created_at": "2026-05-10T12:30:00.000Z",
  "updated_at": "2026-05-10T12:30:00.000Z"
}
```

> **Dashboard UI Tip:** Show `app_id`, `app_key`, and `server_secret` to the client in their dashboard. The `server_secret` should be shown only once (or behind a "reveal" button) since it's the authentication credential.

**Validation Rules:**

| Field | Rule |
|---|---|
| `max_video_quality` | Must be: sd, hd, fhd, 2k, 4k |
| `max_call_video_quality` | Must be: sd, hd, fhd, 2k, 4k |
| `call_ring_timeout` | 10 — 120 seconds |
| `max_call_duration` | 60 — 14400 seconds (1 min — 4 hours) |

### List Client's Projects

```
GET /admin/clients/:clientId/projects?page=1&per_page=20
```

### Get Project Details

Returns the project with client info and live counts.

```
GET /admin/projects/:id
```

**Response (200):**

```json
{
  "id": 1,
  "client_id": 1,
  "name": "My App",
  "app_id": "3847291056",
  "app_key": "a1b2c3d4...",
  "server_secret": "f6e5d4c3...",
  "callback_url": "https://myapp.com/webhooks",
  "callback_secret": "9a8b7c6d...",
  "max_concurrent_rooms": 100,
  "max_participants_per_room": 50,
  "audio_enabled": true,
  "video_enabled": true,
  "max_video_quality": "fhd",
  "calls_enabled": true,
  "call_ring_timeout": 30,
  "max_call_duration": 3600,
  "max_call_video_quality": "hd",
  "region": "me-central1",
  "status": "active",
  "created_at": "2026-05-10T12:30:00.000Z",
  "updated_at": "2026-05-10T12:30:00.000Z",
  "client": {
    "id": 1,
    "name": "Acme Corp",
    "email": "admin@acme.com"
  },
  "active_rooms": 3,
  "active_calls": 1
}
```

### Update Project Settings

```
PUT /admin/projects/:id
```

**Request Body (all optional):**

```json
{
  "name": "My App v2",
  "callback_url": "https://myapp.com/webhooks/utd",
  "max_concurrent_rooms": 200,
  "max_participants_per_room": 100,
  "video_enabled": true,
  "max_video_quality": "fhd",
  "calls_enabled": true,
  "call_ring_timeout": 45,
  "max_call_duration": 7200,
  "max_call_video_quality": "hd",
  "region": "me-central1"
}
```

> **Note:** When setting `callback_url` for the first time, a `callback_secret` is auto-generated. This secret is used to sign webhook events (HMAC-SHA256).

### Suspend / Activate Project

```
PATCH /admin/projects/:id/status
```

```json
{ "status": "suspended" }
```

When a project is suspended, all its API calls return `403 Project is suspended`.

### Regenerate Project Credentials

Generates new `app_key` and `server_secret`. The `app_id` stays the same.

```
POST /admin/projects/:id/regenerate-credentials
```

**Response (200):**

```json
{
  "app_id": "3847291056",
  "app_key": "new_key_here_32chars...",
  "server_secret": "new_secret_here_32chars..."
}
```

> **Warning:** This immediately invalidates the old credentials. The client must update their integration.

---

## 5. Trial Period Management

UTD-Voice doesn't have a built-in trial expiry mechanism. Here's how to implement trial management in the dashboard:

### Approach: Track Trial Expiry in the Dashboard

```
Dashboard Database (your side):
┌──────────────────────────────────────────────┐
│ trial_subscriptions                          │
├──────────────────────────────────────────────┤
│ id                                           │
│ utd_client_id    → maps to UTD-Voice client  │
│ utd_project_id   → maps to UTD-Voice project │
│ trial_starts_at                              │
│ trial_ends_at                                │
│ status           → trial / active / expired  │
└──────────────────────────────────────────────┘
```

### Flow

**1. Client Signs Up for Trial**

```javascript
// 1. Create client in UTD-Voice
const client = await adminFetch('/clients', {
  method: 'POST',
  body: { name, email, phone, company_name },
});

// 2. Create project with trial limits
const project = await adminFetch(`/clients/${client.id}/projects`, {
  method: 'POST',
  body: {
    name: `${company_name} - Trial`,
    max_concurrent_rooms: 5,        // limited for trial
    max_participants_per_room: 10,   // limited for trial
    audio_enabled: true,
    video_enabled: false,            // no video in trial
    calls_enabled: true,
    max_call_duration: 300,          // 5 min max calls
  },
});

// 3. Save trial record in your database
await db('trial_subscriptions').insert({
  utd_client_id: client.id,
  utd_project_id: project.id,
  trial_starts_at: new Date(),
  trial_ends_at: new Date(Date.now() + 3 * 24 * 60 * 60 * 1000), // 3 days
  status: 'trial',
});

// 4. Show credentials to the client
// app_id, app_key, server_secret
```

**2. Cron Job: Suspend Expired Trials**

Run this every hour (or more frequently):

```javascript
// Find expired trials
const expired = await db('trial_subscriptions')
  .where('status', 'trial')
  .where('trial_ends_at', '<=', new Date());

for (const trial of expired) {
  // Suspend the project in UTD-Voice
  await adminFetch(`/projects/${trial.utd_project_id}/status`, {
    method: 'PATCH',
    body: { status: 'suspended' },
  });

  // Update local record
  await db('trial_subscriptions')
    .where('id', trial.id)
    .update({ status: 'expired' });
}
```

**3. Upgrade from Trial to Paid**

```javascript
async function upgradeToFull(trialId) {
  const trial = await db('trial_subscriptions').where('id', trialId).first();

  // Reactivate the project with full limits
  await adminFetch(`/projects/${trial.utd_project_id}/status`, {
    method: 'PATCH',
    body: { status: 'active' },
  });

  // Update project settings to full plan
  await adminFetch(`/projects/${trial.utd_project_id}`, {
    method: 'PUT',
    body: {
      max_concurrent_rooms: 100,
      max_participants_per_room: 50,
      video_enabled: true,
      max_video_quality: 'fhd',
      max_call_duration: 3600,
    },
  });

  await db('trial_subscriptions')
    .where('id', trialId)
    .update({ status: 'active' });
}
```

### Trial Settings Recommendation

| Setting | Trial Value | Full Value |
|---|---|---|
| `max_concurrent_rooms` | 5 | 100+ |
| `max_participants_per_room` | 10 | 50+ |
| `video_enabled` | false | true |
| `calls_enabled` | true | true |
| `max_call_duration` | 300 (5 min) | 3600 (1 hour) |
| `max_video_quality` | sd | fhd / 4k |

---

## 6. Dashboard Statistics

Get a real-time overview of the entire platform.

```
GET /admin/dashboard
```

**Response (200):**

```json
{
  "total_clients": 45,
  "total_projects": 78,
  "active_rooms": 12,
  "active_calls": 3,
  "today": {
    "audio_minutes": 1523.5,
    "video_minutes": 342.8,
    "video_quality": {
      "sd_minutes": 120.3,
      "hd_minutes": 180.5,
      "fhd_minutes": 42.0,
      "two_k_minutes": 0,
      "two_k_plus_minutes": 0
    },
    "total_sessions": 89,
    "total_calls": 34,
    "call_audio_minutes": 256.7,
    "call_video_minutes": 45.2,
    "missed_calls": 5,
    "rejected_calls": 2
  }
}
```

### Suggested Dashboard Cards

| Card | Data Source |
|---|---|
| Total Clients | `total_clients` |
| Total Projects | `total_projects` |
| Active Rooms (live) | `active_rooms` |
| Active Calls (live) | `active_calls` |
| Today's Audio Minutes | `today.audio_minutes` |
| Today's Video Minutes | `today.video_minutes` |
| Today's Total Sessions | `today.total_sessions` |
| Today's Calls | `today.total_calls` |
| Missed Calls Today | `today.missed_calls` |

> **Tip:** Poll this endpoint every 30-60 seconds to keep the dashboard live.

---

## 7. Usage Analytics

### Platform-Wide Daily Usage

```
GET /admin/usage?start_date=2026-05-01&end_date=2026-05-10
```

**Response (200):**

```json
{
  "data": [
    {
      "date": "2026-05-10",
      "audio_minutes": 1523.5,
      "video_minutes": 342.8,
      "sd_minutes": 120.3,
      "hd_minutes": 180.5,
      "fhd_minutes": 42.0,
      "two_k_minutes": 0,
      "two_k_plus_minutes": 0,
      "total_sessions": 89,
      "total_calls": 34,
      "call_audio_minutes": 256.7,
      "call_video_minutes": 45.2,
      "call_sd_minutes": 10.5,
      "call_hd_minutes": 30.2,
      "call_fhd_minutes": 4.5,
      "call_two_k_minutes": 0,
      "call_two_k_plus_minutes": 0,
      "missed_calls": 5,
      "rejected_calls": 2,
      "peak_concurrent_calls": 3
    }
  ]
}
```

- Returns up to 90 days of data
- Both `start_date` and `end_date` are optional

### Project Usage Summary

Get total usage for a specific project.

```
GET /admin/usage/projects/:projectId?start_date=2026-05-01&end_date=2026-05-10
```

**Response (200):**

```json
{
  "project_id": 1,
  "audio_minutes": 523.5,
  "video_minutes": 142.8,
  "video_quality": {
    "sd_minutes": 50.3,
    "hd_minutes": 80.5,
    "fhd_minutes": 12.0,
    "two_k_minutes": 0,
    "two_k_plus_minutes": 0
  },
  "total_sessions": 39,
  "peak_rooms": 8,
  "peak_participants": 23,
  "calls": {
    "total_calls": 15,
    "call_audio_minutes": 100.7,
    "call_video_minutes": 20.2,
    "call_video_quality": {
      "sd_minutes": 5.5,
      "hd_minutes": 12.2,
      "fhd_minutes": 2.5,
      "two_k_minutes": 0,
      "two_k_plus_minutes": 0
    },
    "missed_calls": 2,
    "rejected_calls": 1,
    "peak_concurrent_calls": 2
  }
}
```

### Project Daily Breakdown

```
GET /admin/usage/projects/:projectId/daily?start_date=2026-05-01&end_date=2026-05-10
```

Returns the raw `usage_daily` records for the project (up to 90 days).

### Usage Data for Charts

Use the daily endpoints to build usage charts:

| Chart | X-Axis | Y-Axis | Data Source |
|---|---|---|---|
| Audio Minutes Over Time | date | audio_minutes | `/admin/usage` |
| Video Quality Distribution | quality tier | minutes | video_quality breakdown |
| Daily Sessions | date | total_sessions | `/admin/usage` |
| Daily Calls | date | total_calls | `/admin/usage` |
| Peak Concurrent Rooms | date | peak_rooms | `/admin/usage/projects/:id/daily` |
| Call Success vs Missed | date | total/missed/rejected | calls fields |

---

## 8. Live Room Monitoring

View all currently active rooms across the platform.

```
GET /admin/rooms?page=1&per_page=20&project_id=1
```

| Parameter | Description |
|---|---|
| `page` | Page number |
| `per_page` | Items per page (max 100) |
| `project_id` | Filter by specific project |

**Response (200):**

```json
{
  "data": [
    {
      "id": 42,
      "room_name": "team-meeting",
      "participant_count": 5,
      "max_participants": 50,
      "started_at": "2026-05-10T14:30:00.000Z",
      "project_name": "My App",
      "app_id": "3847291056",
      "client_name": "Acme Corp"
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 3,
    "total_pages": 1
  }
}
```

> **Note:** This only shows rooms with `status = 'active'`. Closed rooms are not included.

---

## 9. Call Monitoring & Statistics

### List All Calls

```
GET /admin/calls?page=1&per_page=20
```

| Parameter | Type | Description |
|---|---|---|
| `page` | number | Page number |
| `per_page` | number | Items per page (max 100) |
| `project_id` | number | Filter by project |
| `client_id` | number | Filter by client |
| `status` | string | Filter: active, ended, missed, rejected, busy, initiating, ringing |
| `type` | string | Filter: voice, video |
| `identity` | string | Filter by caller or callee identity |
| `start_date` | string | Filter from date (YYYY-MM-DD) |
| `end_date` | string | Filter to date (YYYY-MM-DD) |

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "call_id": "call_a1b2c3d4e5f6...",
      "project_id": 1,
      "type": "voice",
      "status": "ended",
      "caller_identity": "user-123",
      "caller_name": "Ahmed",
      "callee_identity": "user-456",
      "callee_name": "Sara",
      "room_name": "call_a1b2c3d4e5f6...",
      "initiated_at": "2026-05-10T14:00:00.000Z",
      "ringing_at": "2026-05-10T14:00:02.000Z",
      "answered_at": "2026-05-10T14:00:05.000Z",
      "ended_at": "2026-05-10T14:05:30.000Z",
      "end_reason": "caller_hangup",
      "duration_seconds": 325,
      "ring_duration_seconds": 5,
      "project_name": "My App",
      "app_id": "3847291056",
      "client_name": "Acme Corp"
    }
  ],
  "pagination": { "page": 1, "per_page": 20, "total": 156, "total_pages": 8 }
}
```

### Call Statistics

```
GET /admin/calls/stats?project_id=1&start_date=2026-05-01&end_date=2026-05-10
```

All query parameters are optional.

**Response (200):**

```json
{
  "total_calls": 156,
  "active_calls": 3,
  "average_duration_seconds": 245,
  "call_audio_minutes": 634.5,
  "call_video_minutes": 89.2,
  "calls_by_status": {
    "ended": 120,
    "missed": 25,
    "rejected": 8,
    "busy": 3
  },
  "calls_by_type": {
    "voice": 130,
    "video": 26
  }
}
```

### Get Single Call Details

```
GET /admin/calls/:id
```

> **Note:** For admin endpoints, use the internal `id` (number), not `call_id` (string).

---

## 10. API Logs

View API access logs for monitoring and debugging.

```
GET /admin/logs?page=1&per_page=50
```

| Parameter | Type | Description |
|---|---|---|
| `page` | number | Page number |
| `per_page` | number | Items per page (max 100) |
| `project_id` | number | Filter by project |
| `method` | string | Filter: GET, POST, PUT, PATCH, DELETE |
| `status_code` | number | Filter by HTTP status code |
| `start_date` | string | Filter from date |
| `end_date` | string | Filter to date |

**Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "project_id": 1,
      "endpoint": "/api/v1/token",
      "method": "POST",
      "ip": "203.0.113.42",
      "status_code": 200,
      "response_time_ms": 45,
      "created_at": "2026-05-10T14:30:00.000Z"
    }
  ],
  "pagination": { "page": 1, "per_page": 50, "total": 1234, "total_pages": 25 }
}
```

### What to Highlight in the Dashboard

| Metric | Meaning |
|---|---|
| High `status_code: 401` count | Client may have wrong credentials |
| High `status_code: 429` count | Client hitting rate limits |
| High `status_code: 403` count | Client is suspended or feature not enabled |
| High `response_time_ms` | Performance issue on our end |

---

## 11. Pagination

All list endpoints use the same pagination format:

### Request Parameters

| Parameter | Default | Max | Description |
|---|---|---|---|
| `page` | 1 | — | Page number |
| `per_page` | 20 | 100 | Items per page |

### Response Format

```json
{
  "data": [ ... ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 156,
    "total_pages": 8
  }
}
```

### Frontend Pagination Example

```javascript
async function fetchClients(page = 1, search = '') {
  const params = new URLSearchParams({ page, per_page: 20 });
  if (search) params.set('search', search);

  const result = await adminFetch(`/clients?${params}`);
  // result.data = array of clients
  // result.pagination = { page, per_page, total, total_pages }
  return result;
}
```

---

## 12. Error Handling

### Standard Error Format

All errors return JSON:

```json
{ "message": "Human-readable error description" }
```

### HTTP Status Codes

| Status | Meaning | Action |
|---|---|---|
| 200 | Success | — |
| 201 | Created | New resource created |
| 401 | Unauthorized | Check admin key |
| 404 | Not Found | Resource doesn't exist |
| 409 | Conflict | Duplicate email, etc. |
| 422 | Validation Error | Check request body |
| 500 | Server Error | Report to backend team |

### Common Errors by Endpoint

**Client Endpoints:**
```
422: "name and email are required"
409: "Email already registered"
409: "Email already in use"
404: "Client not found"
422: "Status must be active or suspended"
```

**Project Endpoints:**
```
404: "Client not found"
422: "name is required"
422: "max_video_quality must be one of: sd, hd, fhd, 2k, 4k"
422: "call_ring_timeout must be between 10 and 120 seconds"
422: "max_call_duration must be between 60 and 14400 seconds"
404: "Project not found"
```

---

## 13. Suggested Dashboard Pages

Here's a recommended page structure for the admin dashboard:

### Page 1: Overview (Dashboard Home)

- Cards: Total Clients, Total Projects, Active Rooms, Active Calls
- Today's usage: Audio Minutes, Video Minutes, Total Sessions, Total Calls
- Charts: Usage trend (last 7/30 days), Call success rate pie chart

**API:** `GET /admin/dashboard` (poll every 30-60s)

### Page 2: Client Management

- Table: Name, Email, Company, Status, Created Date, Actions
- Search bar (filters by name/email/company)
- Status filter dropdown (Active / Suspended)
- Actions: View Details, Edit, Suspend/Activate
- "New Client" button

**APIs:** `GET /admin/clients`, `POST /admin/clients`, `PUT /admin/clients/:id`, `PATCH /admin/clients/:id/status`

### Page 3: Client Details

- Client info card (name, email, phone, company, status, api_key)
- Projects table for this client
- "New Project" button

**APIs:** `GET /admin/clients/:id`, `POST /admin/clients/:clientId/projects`

### Page 4: Project Details

- Project info: name, app_id, status, region
- Credentials section: app_id, app_key, server_secret (with copy buttons, server_secret behind reveal)
- Settings section: room limits, audio/video settings, call settings, callback URL
- Live stats: active rooms count, active calls count
- Usage charts for this project
- Actions: Edit Settings, Regenerate Credentials, Suspend/Activate

**APIs:** `GET /admin/projects/:id`, `PUT /admin/projects/:id`, `PATCH /admin/projects/:id/status`, `POST /admin/projects/:id/regenerate-credentials`, `GET /admin/usage/projects/:id`, `GET /admin/usage/projects/:id/daily`

### Page 5: Live Monitoring

- Active Rooms table (room name, participants, project, client, duration)
- Active Calls table (caller, callee, type, project, duration)
- Auto-refresh every 10-15 seconds

**APIs:** `GET /admin/rooms`, `GET /admin/calls?status=active`

### Page 6: Usage Analytics

- Date range picker
- Charts: Audio/Video minutes over time, Sessions over time, Quality distribution
- Call analytics: Total calls, Avg duration, Success/missed/rejected breakdown
- Per-project breakdown table

**APIs:** `GET /admin/usage`, `GET /admin/calls/stats`

### Page 7: Call History

- Filterable table: all calls across projects
- Filters: status, type, project, client, date range, identity search
- Call detail modal: full timeline (initiated → ringing → answered → ended)

**APIs:** `GET /admin/calls`, `GET /admin/calls/:id`

### Page 8: API Logs

- Filterable table: endpoint, method, status code, response time, IP, timestamp
- Filters: project, method, status code, date range
- Highlight errors (4xx, 5xx) in red

**API:** `GET /admin/logs`

---

## Quick Reference: All Admin Endpoints

| Method | Path | Description |
|---|---|---|
| `POST` | `/admin/clients` | Create client |
| `GET` | `/admin/clients` | List clients |
| `GET` | `/admin/clients/:id` | Get client + projects |
| `PUT` | `/admin/clients/:id` | Update client |
| `PATCH` | `/admin/clients/:id/status` | Suspend/activate client |
| `POST` | `/admin/clients/:clientId/projects` | Create project |
| `GET` | `/admin/clients/:clientId/projects` | List client's projects |
| `GET` | `/admin/projects/:id` | Get project details |
| `PUT` | `/admin/projects/:id` | Update project settings |
| `PATCH` | `/admin/projects/:id/status` | Suspend/activate project |
| `POST` | `/admin/projects/:id/regenerate-credentials` | New app_key + server_secret |
| `GET` | `/admin/dashboard` | Platform overview stats |
| `GET` | `/admin/usage` | Platform daily usage |
| `GET` | `/admin/usage/projects/:id` | Project usage summary |
| `GET` | `/admin/usage/projects/:id/daily` | Project daily breakdown |
| `GET` | `/admin/rooms` | Active rooms |
| `GET` | `/admin/calls` | All calls (filtered) |
| `GET` | `/admin/calls/stats` | Call statistics |
| `GET` | `/admin/calls/:id` | Single call details |
| `GET` | `/admin/logs` | API access logs |
