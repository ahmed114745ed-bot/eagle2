<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Http;

class PusherStatisticsController extends  Controller
{
    public function index()
    {
        $key = config('broadcasting.connections.pusher.key') ?? env('PUSHER_APP_KEY');
        $secret = config('broadcasting.connections.pusher.secret') ?? env('PUSHER_APP_SECRET');
        $appId = config('broadcasting.connections.pusher.app_id') ?? env('PUSHER_APP_ID');
        $cluster = config('broadcasting.connections.pusher.options.cluster') ?? env('PUSHER_APP_CLUSTER');

        if (!$key || !$secret) {
            return back()->withErrors(['pusher' => 'Missing Pusher credentials']);
        }

        $url = "https://api-{$cluster}.pusher.com/apps/{$appId}/channels";

        $response = \Http::withBasicAuth($key, $secret)->get($url, [
            'info' => 'subscription_count,user_count'
        ]);

        info($response);

        if ($response->failed()) {
            return back()->withErrors(['pusher' => 'Failed to fetch channels: ' . $response->body()]);
        }

        $channels = $response->json('channels') ?? [];

        return view('pusher-statistics.index', compact('channels'));
    }
}
