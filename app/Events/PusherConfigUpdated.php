<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when Pusher configuration is updated
 * This allows all Octane workers to be notified immediately
 */
class PusherConfigUpdated
{
    use Dispatchable, SerializesModels;

    public array $config;
    public string $changedKey;
    public string $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(array $config, string $changedKey)
    {
        $this->config = $config;
        $this->changedKey = $changedKey;
        $this->timestamp = now()->toDateTimeString();
    }
}
