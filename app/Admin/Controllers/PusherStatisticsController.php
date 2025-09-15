<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Http;

class PusherStatisticsController extends  Controller
{
    public function index()
    {
        info('pushhhhhher');
        $appId = env('PUSHER_APP_ID');
        $cluster = env('PUSHER_APP_CLUSTER');
        $key = env('PUSHER_APP_KEY');
        $secret = env('PUSHER_APP_SECRET');

        $url = "https://api-{$cluster}.pusher.com/apps/{$appId}/channels";

        $response = Http::withBasicAuth($key, $secret)->get($url, [
            'info' => 'subscription_count,user_count'
        ]);

        if ($response->failed()) {
            return back()->withErrors(['pusher' => 'Failed to fetch channels: ' . $response->body()]);
        }

        $channels = $response->json('channels') ?? [];

        return view('pusher-statistics.index', compact('channels'));
    }
}
