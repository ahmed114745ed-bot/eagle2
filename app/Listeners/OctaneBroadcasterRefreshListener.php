<?php

namespace App\Listeners;

use App\Services\OctaneBroadcasterService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Listener to refresh Pusher broadcaster credentials on every TickReceived event
 * Ensures broadcast events always use fresh database credentials in ALL Octane workers
 * 
 * This listener runs periodically (every N ticks) to check for config changes
 * and update all workers without needing a full Octane restart
 */
class OctaneBroadcasterRefreshListener
{
    /**
     * Track the number of ticks since last check
     */
    private static int $tickCount = 0;

    /**
     * How often to check for config changes (in ticks)
     * Default: every 10 ticks (approximately every 10 seconds with default Octane settings)
     */
    private const CHECK_INTERVAL = 10;

    /**
     * Handle TickReceived events to rebuild broadcaster with fresh credentials
     * This runs on EVERY worker separately, ensuring all workers get updated
     */
    public function handle($event): void
    {
        try {
            self::$tickCount++;

            // Check for immediate update flag (set by PusherConfigObserver)
            $forceUpdate = Cache::has('pusher_config_changed');

            // Periodic check every N ticks OR immediate if flag is set
            if ($forceUpdate || self::$tickCount >= self::CHECK_INTERVAL) {
                $this->refreshPusherConfig($forceUpdate);
                
                // Reset tick counter
                self::$tickCount = 0;
                
                // Clear the force update flag if it was set
                if ($forceUpdate) {
                    Cache::forget('pusher_config_changed');
                }
            }
        } catch (\Throwable $e) {
            Log::error('OctaneBroadcasterRefreshListener Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'pid' => getmypid(),
            ]);
        }
    }

    /**
     * Refresh Pusher configuration from database and rebuild broadcaster
     */
    private function refreshPusherConfig(bool $forced = false): void
    {
        try {
            // Get fresh config from database (bypassing cache)
            $currentConfig = $this->getFreshPusherConfig();
            
            if (!$this->isValidPusherConfig($currentConfig)) {
                return;
            }

            // Check if config actually changed (to avoid unnecessary rebuilds)
            $cachedConfig = Cache::get('octane_worker_pusher_config_' . getmypid());
            
            if (!$forced && $cachedConfig === $this->hashConfig($currentConfig)) {
                // No change detected, skip rebuild
                return;
            }

            // Update runtime config from DB
            OctaneBroadcasterService::updateRuntimeConfigFromDb();

            // Rebuild broadcaster with fresh credentials
            OctaneBroadcasterService::rebuildBroadcaster();

            // Cache the current config hash for this worker
            Cache::put(
                'octane_worker_pusher_config_' . getmypid(), 
                $this->hashConfig($currentConfig),
                3600 // 1 hour
            );

        } catch (\Throwable $e) {
            Log::error('Failed to refresh Pusher config', [
                'error' => $e->getMessage(),
                'pid' => getmypid(),
            ]);
        }
    }

    /**
     * Get fresh Pusher config directly from database
     */
    private function getFreshPusherConfig(): array
    {
        return getPusherConfig();
    }

    /**
     * Check if Pusher config is valid
     */
    private function isValidPusherConfig(array $config): bool
    {
        return !empty($config['app_key']) 
            && !empty($config['app_secret']) 
            && !empty($config['app_id']);
    }

    /**
     * Create a hash of the config to detect changes
     */
    private function hashConfig(array $config): string
    {
        return md5(json_encode([
            'key' => $config['app_key'] ?? '',
            'secret' => $config['app_secret'] ?? '',
            'id' => $config['app_id'] ?? '',
            'cluster' => $config['app_cluster'] ?? '',
        ]));
    }
}
