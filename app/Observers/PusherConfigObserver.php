<?php

namespace App\Observers;

use App\Models\Config;
use App\Services\OctaneBroadcasterService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config as LaravelConfig;

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

        // 1. Clear all caches
        Cache::forget('pusher_config');
        Cache::forget('all_configs');
        Cache::flush();
        
        // 2. Update Laravel config runtime
        $this->updateLaravelConfigRuntime();

        // 3. Rebuild broadcaster with fresh credentials
        if (OctaneBroadcasterService::isOctane()) {
            OctaneBroadcasterService::rebuildBroadcaster();
            // Clear Octane in-memory cache
            Cache::store('octane')->clear();
            // Log timestamp of rebuild
            Cache::put('octane_broadcaster_rebuilt_at', now()->toDateTimeString(), 60 * 60);
        }

        // 4. Log the change
        logger("Config updated: {$config->name} = {$config->value}");
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

            logger('Pusher config updated in runtime');
        } catch (\Exception $e) {
            logger('Error updating Pusher config runtime: ' . $e->getMessage());
        }
    }
}
