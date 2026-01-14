<?php

namespace App\Observers;

use App\Models\Config;
use App\Services\OctaneBroadcasterService;
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
        // Avoid global Cache::flush() to keep unrelated caches; rely on targeted keys
        Cache::put('pusher_config_changed', $meta['timestamp'], 300);
        
        // 2. Update Laravel config runtime
        $this->updateLaravelConfigRuntime();

        // 3. Rebuild broadcaster with fresh credentials
        if (OctaneBroadcasterService::isOctane()) {
            OctaneBroadcasterService::rebuildBroadcaster();
            // Clear Octane in-memory cache
            Cache::store('octane')->clear();
            // Log timestamp of rebuild
            Cache::put('octane_broadcaster_rebuilt_at', $meta['timestamp'], 60 * 60);
        }

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
}
