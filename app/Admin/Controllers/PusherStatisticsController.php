<?php

namespace App\Admin\Controllers;

use Encore\Admin\Layout\Content;

class PusherStatisticsController extends MainController
{
    protected $title = 'Pusher Statistics';
    public $permission_name = 'pusher-statistics';

    public function index(Content $content)
    {
        $data = $this->getChannels();
        $channels = $data['channels'] ?? [];
        $error    = $data['error'] ?? null;

        return parent::index(
            $content
                ->title(__('Pusher Statistics'))
                ->view('pusher-statistics', compact('channels', 'error'))
        );
    }

    private function getChannels()
    {
        $appId   = config('broadcasting.connections.pusher.app_id') ?? env('PUSHER_APP_ID');
        $key     = config('broadcasting.connections.pusher.key') ?? env('PUSHER_APP_KEY');
        $secret  = config('broadcasting.connections.pusher.secret') ?? env('PUSHER_APP_SECRET');
        $cluster = config('broadcasting.connections.pusher.options.cluster') ?? env('PUSHER_APP_CLUSTER');

        $method = "GET";
        $path   = "/apps/{$appId}/channels";

        $params = [
            'auth_key'        => $key,
            'auth_timestamp'  => time(),
            'auth_version'    => '1.0',
            'filter_by_prefix'=> 'presence-',
            'info'            => 'subscription_count,user_count',
        ];

        ksort($params);

        $queryParts = [];
        foreach ($params as $k => $v) {
            if ($k === 'info') {
                $queryParts[] = $k . '=' . $v; // keep comma
            } else {
                $queryParts[] = $k . '=' . rawurlencode($v);
            }
        }
        $queryString = implode('&', $queryParts);

        $stringToSign = "{$method}\n{$path}\n{$queryString}";
        $signature    = hash_hmac('sha256', $stringToSign, $secret);

        $url = "https://api-{$cluster}.pusher.com{$path}?{$queryString}&auth_signature={$signature}";

        try {
            $response = \Http::get($url);

            if ($response->failed()) {
                return [
                    'error' => "Pusher API Error [{$response->status()}]: " . $response->body(),
                    'channels' => [],
                ];
            }

            return [
                'channels' => $response->json('channels') ?? [],
                'error'    => null,
            ];
        } catch (\Exception $e) {
            return [
                'error'    => $e->getMessage(),
                'channels' => [],
            ];
        }
    }
}
