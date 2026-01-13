<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class OctanePusherConfig
{
    /**
     * Static flag to track if we need to reload config (per worker)
     */
    private static bool $needsReload = true;
    private static ?array $cachedConfig = null;

    /**
     * Handle an incoming request.
     * Load Pusher config dynamically (Octane compatibility)
     * Optimized: Only checks database when config changes
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Check if Pusher config was changed - quick cache check
            if (Cache::has('pusher_config_changed')) {
                Cache::forget('pusher_config_changed');
                Cache::forget('pusher_config');
                self::$needsReload = true;
                self::$cachedConfig = null;
                
                // Clear the cached Pusher driver instance
                $this->clearPusherDriver();
                
                Log::info('🔄 Octane: Pusher config change detected, clearing driver cache');
            }
            
            // Load config only if needed
            if (self::$needsReload || self::$cachedConfig === null) {
                self::$cachedConfig = getPusherConfig();
                self::$needsReload = false;
            }
            
            // Apply config
            if (
                self::$cachedConfig &&
                !empty(self::$cachedConfig['app_key']) &&
                !empty(self::$cachedConfig['app_secret']) &&
                !empty(self::$cachedConfig['app_id'])
            ) {
                Config::set('broadcasting.connections.pusher.key', self::$cachedConfig['app_key']);
                Config::set('broadcasting.connections.pusher.secret', self::$cachedConfig['app_secret']);
                Config::set('broadcasting.connections.pusher.app_id', self::$cachedConfig['app_id']);
                Config::set('broadcasting.connections.pusher.options.cluster', self::$cachedConfig['app_cluster'] ?? 'mt1');
            }
        } catch (\Throwable $e) {
            Log::error('❌ OctanePusherConfig middleware error: ' . $e->getMessage());
        }

        return $next($request);
    }
    
    /**
     * Clear the cached Pusher driver using reflection
     */
    private function clearPusherDriver(): void
    {
        try {
            $broadcastManager = app('Illuminate\Broadcasting\BroadcastManager');
            
            $reflection = new \ReflectionClass($broadcastManager);
            $driversProperty = $reflection->getProperty('drivers');
            $driversProperty->setAccessible(true);
            $drivers = $driversProperty->getValue($broadcastManager);
            
            if (isset($drivers['pusher'])) {
                unset($drivers['pusher']);
                $driversProperty->setValue($broadcastManager, $drivers);
            }
        } catch (\Throwable $e) {
            // Silently fail
        }
    }
}
