<?php

namespace App\Listeners;

use App\Services\OctaneBroadcasterService;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Listener to refresh Pusher config before each queue job processes
 * 
 * This ensures ALL queue jobs (including broadcast jobs) use fresh
 * Pusher credentials from database, even without restarting workers.
 * 
 * This listener is registered in EventServiceProvider for JobProcessing event.
 */
class RefreshPusherConfigBeforeJob
{
    /**
     * Track last config hash to avoid unnecessary updates
     */
    private static ?string $lastConfigHash = null;
    
    /**
     * Track last check time
     */
    private static ?int $lastCheckTime = null;
    
    /**
     * Check interval in seconds (to reduce DB queries)
     */
    private const CHECK_INTERVAL = 5;

    /**
     * Handle the event.
     */
    public function handle(JobProcessing $event): void
    {
        try {
            $now = time();
            
            // Check for force update flag (set by PusherConfigObserver)
            $forceUpdate = Cache::has('pusher_config_changed');
            
            // Skip if we checked recently AND no force update
            if (!$forceUpdate && self::$lastCheckTime && ($now - self::$lastCheckTime) < self::CHECK_INTERVAL) {
                return;
            }
            
            self::$lastCheckTime = $now;
            
            // Get fresh config from database
            $freshConfig = getPusherConfig();
            
            if (!$this->isValidConfig($freshConfig)) {
                return;
            }
            
            // Calculate hash to detect changes
            $currentHash = $this->hashConfig($freshConfig);
            
            // Update if forced OR if config actually changed
            if ($forceUpdate || self::$lastConfigHash !== $currentHash) {
                $this->applyFreshConfig($freshConfig);
                self::$lastConfigHash = $currentHash;
                
                Log::info('Queue: Pusher config refreshed before job', [
                    'job' => $event->job->resolveName(),
                    'forced' => $forceUpdate,
                    'pid' => getmypid(),
                    'queue' => $event->job->getQueue(),
                ]);
            }
            
        } catch (\Throwable $e) {
            // Silent fail - don't break job processing
            Log::error('Queue: Failed to refresh Pusher config', [
                'error' => $e->getMessage(),
                'job' => $event->job->resolveName() ?? 'unknown',
            ]);
        }
    }

    /**
     * Apply fresh config to Laravel runtime and rebuild broadcaster
     */
    private function applyFreshConfig(array $config): void
    {
        // Update runtime config
        Config::set([
            'broadcasting.connections.pusher.key' => $config['app_key'],
            'broadcasting.connections.pusher.secret' => $config['app_secret'],
            'broadcasting.connections.pusher.app_id' => $config['app_id'],
            'broadcasting.connections.pusher.options.cluster' => $config['app_cluster'] ?? 'mt1',
        ]);
        
        // Rebuild broadcaster instance with fresh credentials
        OctaneBroadcasterService::rebuildBroadcaster();
    }

    /**
     * Validate config has required fields
     */
    private function isValidConfig(array $config): bool
    {
        return !empty($config['app_key']) 
            && !empty($config['app_secret']) 
            && !empty($config['app_id']);
    }

    /**
     * Generate hash of config for change detection
     */
    private function hashConfig(array $config): string
    {
        return md5(json_encode([
            $config['app_key'] ?? '',
            $config['app_secret'] ?? '',
            $config['app_id'] ?? '',
            $config['app_cluster'] ?? '',
        ]));
    }
}
