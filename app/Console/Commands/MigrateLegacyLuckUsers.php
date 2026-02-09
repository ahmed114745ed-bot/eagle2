<?php

namespace App\Console\Commands;

use App\Models\GiftLog;
use App\Models\UserLuckProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateLegacyLuckUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luck:migrate-legacy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing lucky gift users to Fair Luck system as legacy users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of legacy luck users...');

        // Find all users who have sent lucky gifts (type 6)
        $userIds = GiftLog::whereHas('gift', function($query) {
            $query->where('type', 6);
        })
        ->distinct()
        ->pluck('user_id');

        $count = 0;
        foreach ($userIds as $userId) {
            UserLuckProfile::firstOrCreate(
                ['user_id' => $userId],
                [
                    'total_bets' => 0,
                    'total_profit' => 0,
                    'bet_count' => 0,
                    'win_count' => 0,
                    'is_legacy_user' => true,
                    'first_bet_at' => now(), // Mark migration date as first bet
                ]
            );
            $count++;
        }

        $this->info("Migration completed. Created/Updated profiles for $count users.");
    }
}
