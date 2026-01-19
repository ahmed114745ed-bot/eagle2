<?php

namespace Utd\Achievements\Console;

use Illuminate\Console\Command;
use Utd\Achievements\Entities\UserAchievementLevel;

class ResetUserAchievementMonthly extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'achievement:reset-monthly';

    /**
     * The console command description.
     */
    protected $description = 'Disable user achievements that have expired (end_at <= today)';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = UserAchievementLevel::where('end_at', '<=', now())
            ->where('is_enable', true)
            ->update(['is_enable' => false]);

        $this->info("Disabled {$count} expired user achievement levels.");

        return Command::SUCCESS;
    }
}
