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

        if (!in_array($config->name, $pusherKeys)) {
            return;
        }

        $original = $config->getOriginal('value');
        $new = $config->value;
        if ($original === $new) {
            return;
        }

        $meta = [
            'pid' => getmypid(),
            'hostname' => @gethostname(),
            'octane' => method_exists(app(), 'runningInOctane') ? app()->runningInOctane() : null,
            'route' => optional(request())->route() ? request()->route()->getName() : null,
            'url' => optional(request())->fullUrl(),
            'user_id' => optional(auth())->id(),
            'timestamp' => now()->toDateTimeString(),
        ];

        $mask = function ($value) {
            if (!is_string($value) || $value === '') return $value;
            $len = strlen($value);
            if ($len <= 8) return str_repeat('*', max(0, $len - 2)) . substr($value, -2);
            return substr($value, 0, 4) . str_repeat('*', $len - 8) . substr($value, -4);
        };

        Cache::forget('pusher_config');
        Cache::forget('all_configs');

        Cache::put('pusher_config_changed', $meta['timestamp'], 300);

        $this->clearWorkerConfigCache();

        $this->updateLaravelConfigRuntime();

        if (OctaneBroadcasterService::isOctane()) {
            OctaneBroadcasterService::rebuildBroadcaster();
            Cache::store('octane')->clear();
            Cache::put('octane_broadcaster_rebuilt_at', $meta['timestamp'], 60 * 60);

            try {
                $pusherConfig = getPusherConfig();
                event(new PusherConfigUpdated($pusherConfig, $config->name));
            } catch (\Exception $e) {
                Log::error('Failed to fire PusherConfigUpdated event', ['error' => $e->getMessage()]);
            }
        }

        $this->restartQueueWorkers($meta);

    }


    private function updateLaravelConfigRuntime(): void
    {
        try {
            $pusherConfig = getPusherConfig();

            LaravelConfig::set([
                'broadcasting.connections.pusher.key' => $pusherConfig['app_key'],
                'broadcasting.connections.pusher.secret' => $pusherConfig['app_secret'],
                'broadcasting.connections.pusher.app_id' => $pusherConfig['app_id'],
                'broadcasting.connections.pusher.options.cluster' => $pusherConfig['app_cluster'],
            ]);


        } catch (\Exception $e) {
            Log::error('pusher_runtime_config_update_error', ['message' => $e->getMessage()]);
        }
    }


    private function clearWorkerConfigCache(): void
    {
        try {

        } catch (\Exception $e) {
            Log::error('pusher_worker_cache_clear_error', ['message' => $e->getMessage()]);
        }
    }

    private function restartQueueWorkers(array $meta): void
    {
        try {
            Cache::put('pusher_config_changed', $meta['timestamp'], 3600);
            Artisan::call('queue:restart');
            Cache::put('queue_restart_requested_at', $meta['timestamp'], 3600);

        } catch (\Exception $e) {
            Log::error('queue_workers_restart_failed', [
                'error' => $e->getMessage(),
                'timestamp' => $meta['timestamp'],
            ]);
        }
    }
}
