<?php

namespace App\Listeners;

use App\Services\OctaneBroadcasterService;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;



class RefreshPusherConfigBeforeJob
{
  
    private static ?string $lastConfigHash = null;
    

    private static ?int $lastCheckTime = null;
    
 
    private const CHECK_INTERVAL = 0;


    public function handle(JobProcessing $event): void
    {

        try {
            $now = time();
            
            $forceUpdate = Cache::has('pusher_config_changed');
            
            if (!$forceUpdate && self::CHECK_INTERVAL > 0 && self::$lastCheckTime && ($now - self::$lastCheckTime) < self::CHECK_INTERVAL) {
                return;
            }
            
            self::$lastCheckTime = $now;
            
            $freshConfig = getPusherConfig();
            
            if (!$this->isValidConfig($freshConfig)) {
                return;
            }
            
            $currentHash = $this->hashConfig($freshConfig);
            
            if ($forceUpdate || self::$lastConfigHash !== $currentHash) {
                $this->applyFreshConfig($freshConfig);
                
                $wasChanged = self::$lastConfigHash !== null && self::$lastConfigHash !== $currentHash;
                self::$lastConfigHash = $currentHash;
                
                if ($forceUpdate) {
                    Cache::forget('pusher_config_changed');
                }
                
            }
            
        } catch (\Throwable $e) {
            Log::error('Queue: Failed to refresh Pusher config', [
                'error' => $e->getMessage(),
                'job' => $event->job->resolveName() ?? 'unknown',
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
