<?php

namespace App\Broadcasting;

use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;

/**
 * Custom Pusher Broadcaster that ALWAYS reads from database
 * Octane-compatible - checks for config changes on EVERY broadcast
 */
class DatabaseDrivenPusherBroadcaster extends PusherBroadcaster
{
    /**
     * Track current config hash to detect changes
     */
    private static ?string $currentConfigHash = null;
    
    /**
     * Create a new broadcaster instance.
     */
    public function __construct()
    {
        // Read fresh from database
        $config = $this->getFreshConfigFromDatabase();
        
        Log::info('🔵 DatabaseDrivenPusherBroadcaster.__construct', [
            'app_id'      => $config['app_id'] ?? 'NULL',
            'app_key'     => $config['app_key'] ?? 'NULL',
            'app_secret'  => !empty($config['app_secret']) ? '***SET(' . strlen($config['app_secret']) . ' chars)***' : '***EMPTY***',
            'app_cluster' => $config['app_cluster'] ?? 'NULL',
        ]);
        
        // Create Pusher instance with DB config
        $pusher = new Pusher(
            $config['app_key'],
            $config['app_secret'],
            $config['app_id'],
            [
                'cluster' => $config['app_cluster'] ?? 'mt1',
                'useTLS' => true,
            ]
        );
        
        // Store config hash
        self::$currentConfigHash = $this->hashConfig($config);
        
        // Call parent constructor with Pusher instance
        parent::__construct($pusher);
    }
    
    /**
     * Broadcast the given event - CHECKS FOR CONFIG CHANGES FIRST
     * This is the key method that runs on EVERY broadcast
     *
     * @param  array  $channels
     * @param  string  $event
     * @param  array  $payload
     * @return void
     */
    public function broadcast(array $channels, $event, array $payload = [])
    {
        // ⭐ CRITICAL: Check if config changed before broadcasting
        $this->refreshPusherIfConfigChanged();
        
        return parent::broadcast($channels, $event, $payload);
    }
    
    /**
     * Check if Pusher config changed and rebuild if needed
     */
    private function refreshPusherIfConfigChanged(): void
    {
        try {
            // Check for force update flag
            $forceUpdate = Cache::has('pusher_config_changed');
            
            // Get fresh config
            $freshConfig = $this->getFreshConfigFromDatabase();
            $newHash = $this->hashConfig($freshConfig);
            
            // Rebuild if config changed or forced
            if ($forceUpdate || self::$currentConfigHash !== $newHash) {
                // Create new Pusher instance
                $this->pusher = new Pusher(
                    $freshConfig['app_key'],
                    $freshConfig['app_secret'],
                    $freshConfig['app_id'],
                    [
                        'cluster' => $freshConfig['app_cluster'] ?? 'mt1',
                        'useTLS' => true,
                    ]
                );
                
                self::$currentConfigHash = $newHash;
                
                // Clear the flag after applying
                if ($forceUpdate) {
                    Cache::forget('pusher_config_changed');
                }
            }
        } catch (\Throwable $e) {
            Log::error('DatabaseDrivenPusherBroadcaster.refresh_error', [
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Generate config hash for change detection
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
    
    /**
     * Get fresh Pusher config from database
     * No caching - direct DB read
     */
    private function getFreshConfigFromDatabase(): array
    {
        try {
            $config = getPusherConfig();
            
            // Fallback to env if DB is empty
            if (empty($config['app_id']) || empty($config['app_key'])) {
                Log::warning('DatabaseDrivenPusherBroadcaster.fallback_to_env', [
                    'reason' => 'Database config empty or invalid',
                ]);
                
                return [
                    'app_id' => env('PUSHER_APP_ID'),
                    'app_key' => env('PUSHER_APP_KEY'),
                    'app_secret' => env('PUSHER_APP_SECRET'),
                    'app_cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
                ];
            }
            
            return $config;
            
        } catch (\Throwable $e) {
            Log::error('DatabaseDrivenPusherBroadcaster.error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            
            // Fallback to env on error
            return [
                'app_id' => env('PUSHER_APP_ID'),
                'app_key' => env('PUSHER_APP_KEY'),
                'app_secret' => env('PUSHER_APP_SECRET'),
                'app_cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
            ];
        }
    }
}
