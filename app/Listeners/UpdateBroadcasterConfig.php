<?php

namespace App\Listeners;

use App\Events\PusherConfigUpdated;
use App\Services\OctaneBroadcasterService;
use Illuminate\Support\Facades\Log;

/**
 * Listener to handle PusherConfigUpdated event
 * Updates broadcaster configuration when Pusher settings change
 */
class UpdateBroadcasterConfig
{
    /**
     * Handle the event.
     */
    public function handle(PusherConfigUpdated $event): void
    {
        try {
            // Update runtime config and rebuild broadcaster
            OctaneBroadcasterService::updateRuntimeConfigFromDb();
            OctaneBroadcasterService::rebuildBroadcaster();

            Log::info('Broadcaster updated via PusherConfigUpdated event', [
                'pid' => getmypid(),
                'changed_key' => $event->changedKey,
                'timestamp' => $event->timestamp,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to update broadcaster via event', [
                'error' => $e->getMessage(),
                'pid' => getmypid(),
            ]);
        }
    }
}
