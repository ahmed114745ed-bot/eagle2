<?php

namespace App\Listeners;

use App\Services\OctaneBroadcasterService;
use Illuminate\Support\Facades\Config;
use Laravel\Octane\Events\RequestReceived;

class RefreshPusherConfigListener
{
    /**
     * Handle the event.
     * 
     * This listener refreshes the Pusher configuration from the database
     * on EVERY request, ensuring that runtime config is always up-to-date.
     * 
     * This is executed as part of RequestReceived in Octane,
     * so it runs before any controllers/middleware.
     */
    public function handle(RequestReceived $event): void
    {
        try {
            // Always read fresh Pusher config from database
            $pusherConfig = getPusherConfig();

            if (
                $pusherConfig &&
                !empty($pusherConfig['app_key']) &&
                !empty($pusherConfig['app_secret']) &&
                !empty($pusherConfig['app_id'])
            ) {
                // Update runtime config with fresh values from database
                Config::set([
                    'broadcasting.connections.pusher.key' => $pusherConfig['app_key'],
                    'broadcasting.connections.pusher.secret' => $pusherConfig['app_secret'],
                    'broadcasting.connections.pusher.app_id' => $pusherConfig['app_id'],
                    'broadcasting.connections.pusher.options.cluster' => $pusherConfig['app_cluster'] ?? 'mt1',
                ]);

                // Rebuild broadcaster with fresh credentials if in Octane
                if (OctaneBroadcasterService::isOctane()) {
                    OctaneBroadcasterService::rebuildBroadcaster();
                }
            }
        } catch (\Throwable $e) {
            // Silently fail - don't break the request
            // Log only if needed: \Log::error('RefreshPusherConfigListener error', ['error' => $e->getMessage()]);
        }
    }
}

