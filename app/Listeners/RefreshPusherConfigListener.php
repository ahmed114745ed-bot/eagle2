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
     * ONLY when the config has changed (detected via cache flag).
     * This is more efficient than flushing BroadcastManager on every request.
     */
    public function handle(RequestReceived $event): void
    {
        try {
            // Check if Pusher config was changed
            if (Cache::pull('pusher_config_changed')) {
                // Flush the BroadcastManager to force recreation with new config
                if ($event->app->resolved('Illuminate\Broadcasting\BroadcastManager')) {
                    $event->app->forgetInstance('Illuminate\Broadcasting\BroadcastManager');
                }
                
                // Get fresh config from database (cache was already cleared)
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
            }
        } catch (\Throwable $e) {
            // Silently fail - don't break the request
        }
    }
}
