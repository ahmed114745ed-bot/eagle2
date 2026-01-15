<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Force Pusher config refresh from database on every request
 * Ensures Octane workers always use latest database values
 */
class ForcePusherRefresh
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // ⭐ Step 1: Read fresh from database
            $config = getPusherConfig();
            
            if ($config && !empty($config['app_key']) && !empty($config['app_id'])) {
                // ⭐ Step 2: Update runtime config
                Config::set('broadcasting.connections.pusher.key', $config['app_key']);
                Config::set('broadcasting.connections.pusher.secret', $config['app_secret']);
                Config::set('broadcasting.connections.pusher.app_id', $config['app_id']);
                Config::set('broadcasting.connections.pusher.options.cluster', $config['app_cluster'] ?? 'mt1');
                
                // ⭐ Step 3: Force clear BroadcastManager drivers cache
                // This ensures next broadcast() call creates fresh instance
                $this->clearBroadcastManagerDrivers();
                
             
            }
            
        } catch (\Throwable $e) {
            Log::error('force_pusher_refresh.error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
        }

        return $next($request);
    }
    
    /**
     * Clear BroadcastManager drivers cache using reflection
     * Forces fresh broadcaster creation on next use
     */
    private function clearBroadcastManagerDrivers(): void
    {
        try {
            $broadcastManager = app(BroadcastManager::class);
            
            $reflection = new \ReflectionClass($broadcastManager);
            $driversProperty = $reflection->getProperty('drivers');
            $driversProperty->setAccessible(true);
            
            // Clear all drivers - forces fresh instantiation
            $driversProperty->setValue($broadcastManager, []);
            
        } catch (\Throwable $e) {
            // Silently fail - not critical
            Log::debug('clear_broadcast_drivers.failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
