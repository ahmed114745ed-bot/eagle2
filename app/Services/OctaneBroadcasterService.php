<?php

namespace App\Services;

use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Broadcasting\BroadcastManager;
use Pusher\Pusher;

/**
 * Service to manage dynamic broadcaster instantiation in Octane
 * Ensures that broadcaster always uses fresh Pusher credentials from database
 */
class OctaneBroadcasterService
{
    /**
     * Rebuild the Pusher broadcaster with fresh credentials from database
     * Call this before broadcasting events in Octane environment
     */
    public static function rebuildBroadcaster(): void
    {
        if (!app()->bound('broadcaster')) {
            return;
        }

        try {
            // Get fresh Pusher credentials from database
            $credentials = getPusherConfig();

            if (!$credentials['app_id'] || !$credentials['app_key'] || !$credentials['app_secret']) {
                return; // Invalid credentials, skip rebuild
            }

            // Create fresh Pusher instance
            $pusher = new Pusher(
                $credentials['app_key'],
                $credentials['app_secret'],
                $credentials['app_id'],
                [
                    'cluster' => $credentials['app_cluster'] ?? 'mt1',
                    'useTLS' => true,
                ]
            );

            // Get the broadcast manager
            $broadcastManager = app('broadcast');

            // Create new Pusher broadcaster
            $broadcaster = new PusherBroadcaster($pusher);

            // Replace the cached broadcaster in the manager
            $broadcastManager->extend('pusher', function ($app) use ($broadcaster) {
                return $broadcaster;
            });

            // Force re-resolution of the driver
            if (method_exists($broadcastManager, 'purge')) {
                $broadcastManager->purge('pusher');
            }

            logger('OctaneBroadcasterService: Broadcaster rebuilt with fresh credentials');
        } catch (\Exception $e) {
            logger('OctaneBroadcasterService Error: ' . $e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * Rebuild broadcaster with TTL (useful for TickReceived events)
     * Rebuilds every N ticks to ensure fresh credentials periodically
     */
    public static function rebuildBroadcasterPeriodically(int $tickInterval = 10): void
    {
        static $tickCount = 0;

        $tickCount++;

        if ($tickCount >= $tickInterval) {
            static::rebuildBroadcaster();
            $tickCount = 0;
        }
    }

    /**
     * Check if running in Octane environment
     */
    public static function isOctane(): bool
    {
        return app()->runningInOctane();
    }
}
