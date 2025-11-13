<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class UpdateOfflineUsers extends Command
{
    protected $signature = 'users:update-offline';
    protected $description = '';

    public function handle()
    {
        $threshold = now()->subMinutes(15);

        $count = User::where('online', true)
            ->where(function ($q) use ($threshold) {
                $q->whereNull('last_seen_at')->orWhere('last_seen_at', '<', $threshold);
            })
            ->update(['online' => false]);

        $this->info("Done");
    }
}
