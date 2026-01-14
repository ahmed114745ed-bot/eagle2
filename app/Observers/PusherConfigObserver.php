<?php

namespace App\Observers;

use App\Models\Config;
use App\Services\OctaneBroadcasterService;
use App\Events\PusherConfigUpdated;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config as LaravelConfig;
use Illuminate\Support\Facades\Log;

/**
 * Observer for Config model
 * Automatically updates broadcaster and clears caches when Pusher credentials change
 */
class PusherConfigObserver
{
    /**
     * Handle the Config "updating" event - before save
     */
    public function updating(Config $config): void
    {
        // No action needed before update
    }

    /**
     * Handle the Config "updated" event - after save
     * Refresh broadcaster if Pusher credentials changed
     */
    public function updated(Config $config): void
    {
        $this->handlePusherConfigChange($config);
    }

    /**
     * Handle the Config "created" event
     */
    public function created(Config $config): void
    {
        $this->handlePusherConfigChange($config);
    }

    /**
     * Handle Pusher config changes
     */
    private function handlePusherConfigChange(Config $config): void
    {
        $pusherKeys = ['pusher_app_id', 'pusher_app_key', 'pusher_app_secret', 'pusher_app_cluster'];

        // Only act if a Pusher-related config was changed
        if (!in_array($config->name, $pusherKeys)) {
            return;
        }

        $original = $config->getOriginal('value');
        $new = $config->value;
        if ($original === $new) {
            // No effective change; avoid redundant rebuilds
            return;
        }

        // Extra debug meta
        $meta = [
            'pid' => getmypid(),
            'hostname' => @gethostname(),
            'octane' => method_exists(app(), 'runningInOctane') ? app()->runningInOctane() : null,
            'route' => optional(request())->route() ? request()->route()->getName() : null,
            'url' => optional(request())->fullUrl(),
            'user_id' => optional(auth())->id(),
            'timestamp' => now()->toDateTimeString(),
        ];

        // Safe masking
        $mask = function ($value) {
            if (!is_string($value) || $value === '') return $value;
            $len = strlen($value);
            if ($len <= 8) return str_repeat('*', max(0, $len - 2)) . substr($value, -2);
            return substr($value, 0, 4) . str_repeat('*', $len - 8) . substr($value, -4);
        };

        // 1. Clear relevant caches
        Cache::forget('pusher_config');
        Cache::forget('all_configs');
        
        // Set flag to trigger immediate update in ALL Octane workers
        // This flag is checked by OctaneBroadcasterRefreshListener in TickReceived event
        Cache::put('pusher_config_changed', $meta['timestamp'], 300);
        
        // Also clear per-worker cache to force config reload on next tick
        $this->clearWorkerConfigCache();
        
        // 2. Update Laravel config runtime
        $this->updateLaravelConfigRuntime();

        // 3. Rebuild broadcaster with fresh credentials
        if (OctaneBroadcasterService::isOctane()) {
            OctaneBroadcasterService::rebuildBroadcaster();
            // Clear Octane in-memory cache
            Cache::store('octane')->clear();
            // Log timestamp of rebuild
            Cache::put('octane_broadcaster_rebuilt_at', $meta['timestamp'], 60 * 60);
            
            // Fire event to notify all Octane workers about config change
            try {
                $pusherConfig = getPusherConfig();
                event(new PusherConfigUpdated($pusherConfig, $config->name));
            } catch (\Exception $e) {
                Log::error('Failed to fire PusherConfigUpdated event', ['error' => $e->getMessage()]);
            }
        }

        // 5. ⭐ CRITICAL: Restart Queue Workers to pick up new Pusher config
        // Queue workers cache broadcaster instance and won't see DB changes without restart
        $this->restartQueueWorkers($meta);

        // 4. Structured log with diff and meta
        Log::info('pusher_config_change', [
            'meta' => $meta,
            'name' => $config->name,
            'original' => $mask($original),
            'new' => $mask($new),
        ]);
    }

    /**
     * Update Laravel's runtime config from database
     * This makes Config::get('broadcasting.connections.pusher.*') work
     */
    private function updateLaravelConfigRuntime(): void
    {
        try {
            $pusherConfig = getPusherConfig();

            // Update config at runtime (works without reload)
            LaravelConfig::set([
                'broadcasting.connections.pusher.key' => $pusherConfig['app_key'],
                'broadcasting.connections.pusher.secret' => $pusherConfig['app_secret'],
                'broadcasting.connections.pusher.app_id' => $pusherConfig['app_id'],
                'broadcasting.connections.pusher.options.cluster' => $pusherConfig['app_cluster'],
            ]);

            Log::info('pusher_runtime_config_updated');
        } catch (\Exception $e) {
            Log::error('pusher_runtime_config_update_error', ['message' => $e->getMessage()]);
        }
    }

    /**
     * Clear worker-specific config cache to force reload
     * This ensures all Octane workers will detect the change on next tick
     */
    private function clearWorkerConfigCache(): void
    {
        try {
            // Clear config cache for all potential worker PIDs
            // We can't know exact PIDs, so we set a global flag instead
            // The OctaneBroadcasterRefreshListener will handle clearing its own cache
            
            // Log the clear operation
            Log::info('pusher_worker_cache_clear_requested', [
                'timestamp' => now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            Log::error('pusher_worker_cache_clear_error', ['message' => $e->getMessage()]);
        }
    }

    /**
     * Restart Queue Workers to pick up new Pusher configuration
     * 
     * Queue workers cache the broadcaster instance at startup.
     * When Pusher credentials change in DB, queue workers continue
     * using old credentials until restarted.
     * 
     * This method signals all queue workers to restart gracefully
     * after completing their current job.
     */
    private function restartQueueWorkers(array $meta): void
    {
        try {
            // ⭐ CRITICAL: Set a flag in cache that RefreshPusherConfigBeforeJob checks
            // This ensures queue workers will refresh on NEXT job even if restart fails
            Cache::put('pusher_config_changed', $meta['timestamp'], 3600);
            
            // Signal queue workers to restart after current job
            // This is graceful - workers finish current job then restart
            Artisan::call('queue:restart');
            
            Log::info('queue_workers_restart_signaled', [
                'reason' => 'pusher_config_changed',
                'timestamp' => $meta['timestamp'],
                'triggered_by' => $meta['user_id'] ?? 'system',
                'cache_driver' => config('cache.default'),
            ]);
            
            // Also set a cache flag so we can track when restart was requested
            Cache::put('queue_restart_requested_at', $meta['timestamp'], 3600);
            
        } catch (\Exception $e) {
            Log::error('queue_workers_restart_failed', [
                'error' => $e->getMessage(),
                'timestamp' => $meta['timestamp'],
            ]);
        }
    }
}
