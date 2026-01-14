<?php

namespace App\Listeners;

use App\Services\OctaneBroadcasterService;

/**
 * Listener to refresh Pusher broadcaster credentials on every TickReceived event
 * Ensures broadcast events always use fresh database credentials
 */
class OctaneBroadcasterRefreshListener
{
    /**
     * Handle TickReceived events to rebuild broadcaster with fresh credentials
     */
    public function handle($event): void
    {
        try {
            // Only act when a change flag is set to avoid unnecessary work
            if (\Illuminate\Support\Facades\Cache::has('pusher_config_changed')) {
                // Update runtime config from DB so Config::get() reflects changes
                OctaneBroadcasterService::updateRuntimeConfigFromDb();

                // Rebuild broadcaster to use fresh credentials
                OctaneBroadcasterService::rebuildBroadcaster();

                // Clear change flag after processing
                \Illuminate\Support\Facades\Cache::forget('pusher_config_changed');
            }
        } catch (\Exception $e) {
            logger('OctaneBroadcasterRefreshListener Error: ' . $e->getMessage());
        }
    }
}
