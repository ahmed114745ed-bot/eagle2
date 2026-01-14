<?php

namespace App\Broadcasting;

use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;

/**
 * Custom Pusher Broadcaster that ALWAYS reads from database
 * Octane-compatible - no caching, fresh DB read every time
 */
class DatabaseDrivenPusherBroadcaster extends PusherBroadcaster
{
    /**
     * Create a new broadcaster instance.
     * ALWAYS reads fresh from database - bypasses all caches
     */
    public function __construct()
    {
        // Read fresh from database every single time
        $config = $this->getFreshConfigFromDatabase();
        
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
        
        // Call parent constructor with Pusher instance
        parent::__construct($pusher);
        
        Log::debug('DatabaseDrivenPusherBroadcaster.created', [
            'app_id' => $config['app_id'],
            'cluster' => $config['app_cluster'],
            'timestamp' => now()->toDateTimeString(),
        ]);
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
