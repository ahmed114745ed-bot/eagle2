<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Laravel\Octane\Events\RequestReceived;

class RefreshPusherConfigListener
{
    /**
     * Handle the event.
     * 
     * This listener refreshes the Pusher configuration from the database
     * on each request to ensure changes made in Admin Settings take effect
     * immediately without requiring an Octane restart.
     */
    public function handle(RequestReceived $event): void
    {
        try {
            // Get the pusher config (will use cache if available)
            $config = getPusherConfig();

            if (
                $config &&
                !empty($config['app_key']) &&
                !empty($config['app_secret']) &&
                !empty($config['app_id'])
            ) {
                Config::set('broadcasting.connections.pusher.key', $config['app_key']);
                Config::set('broadcasting.connections.pusher.secret', $config['app_secret']);
                Config::set('broadcasting.connections.pusher.app_id', $config['app_id']);
                Config::set('broadcasting.connections.pusher.options.cluster', $config['app_cluster'] ?? 'mt1');
            }
        } catch (\Throwable $e) {
            // Silently fail - don't break the request
        }
    }
}
