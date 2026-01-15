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
     * 
     * ⭐ CRITICAL: This purges the cached driver FIRST, then when broadcast()
     * is called, BroadcastManager will create a new instance using our
     * DatabaseDrivenPusherBroadcaster which reads fresh from DB.
     */
    public static function rebuildBroadcaster(): void
    {
        try {
            $broadcastManager = app('broadcast');
            
            $reflection = new \ReflectionClass($broadcastManager);
            $driversProperty = $reflection->getProperty('drivers');
            $driversProperty->setAccessible(true);
            $driversProperty->setValue($broadcastManager, []);
            
            \Illuminate\Support\Facades\Cache::forget('pusher_config_changed');

            logger('OctaneBroadcasterService: Broadcaster cache cleared - will rebuild on next use', [
                'pid' => getmypid(),
                'timestamp' => now()->toDateTimeString(),
            ]);
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
     * Update Laravel runtime config from DB values so Config::get() reflects changes
     */
    public static function updateRuntimeConfigFromDb(): void
    {
        try {
            $pusherConfig = getPusherConfig();

            \Illuminate\Support\Facades\Config::set([
                'broadcasting.connections.pusher.key' => $pusherConfig['app_key'],
                'broadcasting.connections.pusher.secret' => $pusherConfig['app_secret'],
                'broadcasting.connections.pusher.app_id' => $pusherConfig['app_id'],
                'broadcasting.connections.pusher.options.cluster' => $pusherConfig['app_cluster'],
            ]);

            \Illuminate\Support\Facades\Cache::put('octane_broadcaster_rebuilt_at', now()->toDateTimeString(), 60 * 60);
            logger('OctaneBroadcasterService: Runtime config updated from DB');
        } catch (\Throwable $e) {
            logger('OctaneBroadcasterService updateRuntimeConfigFromDb error: ' . $e->getMessage());
        }
    }

    /**
     * Check if running in Octane environment
     */
    public static function isOctane(): bool
    {
        try {
            if (method_exists(app(), 'runningInOctane')) {
                return app()->runningInOctane();
            }
            
            return class_exists('Laravel\Octane\Octane');
        } catch (\Exception $e) {
            return false;
        }
    }
}
