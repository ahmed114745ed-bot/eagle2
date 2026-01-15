<?php

namespace App\Jobs\Middleware;

use App\Services\OctaneBroadcasterService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;


class RefreshPusherConfigMiddleware
{
   
    private static ?string $lastConfigHash = null;
    

    private static ?int $lastCheckTime = null;
    
  
    private const CHECK_INTERVAL_SECONDS = 5;

    /**
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

  
    private function refreshPusherConfigIfNeeded(): void
    {
        try {
            $now = time();
            
            if (self::$lastCheckTime && ($now - self::$lastCheckTime) < self::CHECK_INTERVAL_SECONDS) {
                if (!Cache::has('pusher_config_changed')) {
                    return;
                }
            }
            
            self::$lastCheckTime = $now;
            
            $forceUpdate = Cache::has('pusher_config_changed');
            
            $freshConfig = getPusherConfig();
            
            if (!$this->isValidConfig($freshConfig)) {
                return;
            }
            
            $currentHash = $this->hashConfig($freshConfig);
            
            if ($forceUpdate || self::$lastConfigHash !== $currentHash) {
                $this->applyFreshConfig($freshConfig);
                self::$lastConfigHash = $currentHash;
            }
            
        } catch (\Throwable $e) {
            Log::error('QueueMiddleware: Failed to refresh Pusher config', [
                'error' => $e->getMessage(),
            ]);
        }
    }


    private function applyFreshConfig(array $config): void
    {
        Config::set([
            'broadcasting.connections.pusher.key' => $config['app_key'],
            'broadcasting.connections.pusher.secret' => $config['app_secret'],
            'broadcasting.connections.pusher.app_id' => $config['app_id'],
            'broadcasting.connections.pusher.options.cluster' => $config['app_cluster'] ?? 'mt1',
        ]);
        
        OctaneBroadcasterService::rebuildBroadcaster();
    }

   
    private function isValidConfig(array $config): bool
    {
        return !empty($config['app_key']) 
            && !empty($config['app_secret']) 
            && !empty($config['app_id']);
    }

  
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
