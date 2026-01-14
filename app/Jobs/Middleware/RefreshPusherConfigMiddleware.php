<?php

namespace App\Jobs\Middleware;

use App\Services\OctaneBroadcasterService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Middleware to refresh Pusher config before processing broadcast jobs
 * 
 * This ensures queue workers always use fresh Pusher credentials from database,
 * even without restarting the worker process.
 * 
 * Usage in Job class:
 * public function middleware()
 * {
 *     return [new \App\Jobs\Middleware\RefreshPusherConfigMiddleware];
 * }
 */
class RefreshPusherConfigMiddleware
{
    /**
     * Track last config hash to avoid unnecessary updates
     */
    private static ?string $lastConfigHash = null;
    
    /**
     * Track last check time to avoid checking on every single job
     */
    private static ?int $lastCheckTime = null;
    
    /**
     * Minimum seconds between config checks (to reduce DB queries)
     */
    private const CHECK_INTERVAL_SECONDS = 5;

    /**
     * Process the queued job.
     *
     * @param  mixed  $job
     * @param  callable  $next
     * @return mixed
     */
    public function handle($job, $next)
    {
        $this->refreshPusherConfigIfNeeded();
        
        return $next($job);
    }

    /**
     * Refresh Pusher config if changed or check interval passed
     */
    private function refreshPusherConfigIfNeeded(): void
    {
        try {
            $now = time();
            
            // Skip if we checked recently (within CHECK_INTERVAL_SECONDS)
            if (self::$lastCheckTime && ($now - self::$lastCheckTime) < self::CHECK_INTERVAL_SECONDS) {
                // But still check for force-update flag
                if (!Cache::has('pusher_config_changed')) {
                    return;
                }
            }
            
            self::$lastCheckTime = $now;
            
            // Check if force update is requested (set by PusherConfigObserver)
            $forceUpdate = Cache::has('pusher_config_changed');
            
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
                
                Log::debug('QueueMiddleware: Pusher config refreshed', [
                    'forced' => $forceUpdate,
                    'pid' => getmypid(),
                ]);
            }
            
        } catch (\Throwable $e) {
            Log::error('QueueMiddleware: Failed to refresh Pusher config', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Apply fresh config to Laravel runtime
     */
    private function applyFreshConfig(array $config): void
    {
        Config::set([
            'broadcasting.connections.pusher.key' => $config['app_key'],
            'broadcasting.connections.pusher.secret' => $config['app_secret'],
            'broadcasting.connections.pusher.app_id' => $config['app_id'],
            'broadcasting.connections.pusher.options.cluster' => $config['app_cluster'] ?? 'mt1',
        ]);
        
        // Rebuild broadcaster instance
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
