<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Modules\CP\Console\WeeklyCpWinnerConsole;
use Modules\TribeReward\Jobs\AgencyTribeRewardJob;
use Modules\TribeReward\Jobs\CleanExpiredAgencyRewardsJob;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\UpdateRoomUserNowCron::class,
        Commands\OpenStatusAppFeature::class,
        Commands\CloseStatusAppFeature::class,
        Commands\DeleteTrashedUsers::class,
        Commands\FreezeUsersCommand::class,
        WeeklyCpWinnerConsole::class
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('remove-background:cron')
            ->dailyAt('00:00')
            ->timezone(getTimezone())
            ->appendOutputTo(storage_path('logs/remove-background-cron.log'))
            ->runInBackground();

        $schedule->command('users:reset-monthly-diamond')
            ->monthly()
            ->timezone(getTimezone())
            ->appendOutputTo(storage_path('logs/users-reset-monthly-diamond.log'))
            ->runInBackground();

        $schedule->command('users:reset-monthly-days')
            ->monthlyOn(1, '00:00')
            ->timezone(getTimezone())
            ->appendOutputTo(storage_path('logs/users-reset-monthly-days.log'))
            ->runInBackground();

        $schedule->command('users:reset-today-days')
            ->dailyAt('00:00')
            ->timezone(getTimezone())
            ->appendOutputTo(storage_path('logs/users-reset-today-days.log'))
            ->runInBackground();

        $schedule->command('update-gift-weakly:cron')
            ->weeklyOn(0, '00:00')
            ->timezone(getTimezone())
            ->appendOutputTo(storage_path('logs/update-gift-weekly-cron.log'))
            ->runInBackground();

        $schedule->command('app:reset-top-room-rank')
            ->dailyAt('00:00')
            ->timezone(getTimezone())
            ->appendOutputTo(storage_path('logs/app-reset-top-room-rank.log'))
            ->runInBackground();

        $schedule->command('redis:get_data')
            ->everyFiveMinutes()
            ->appendOutputTo(storage_path('logs/redis-get-data.log'))
            ->runInBackground();

            $schedule->command('update-room-ban')
            ->everyFiveMinutes()
            ->appendOutputTo(storage_path('logs/update-room-ban'))
            ->runInBackground();


        $schedule->command('weekly-star-winner')
            ->dailyAt('00:00')
            ->timezone(getTimezone())
            ->appendOutputTo(storage_path('logs/weekly-star-winner.log'))
            ->runInBackground();

        $schedule->command('weekly-star-update')
            ->dailyAt('00:00')
            ->timezone(getTimezone())
            ->appendOutputTo(storage_path('logs/weekly-star-update.log'))
            ->runInBackground();

        $schedule->command('pk-event-winner')
            ->dailyAt('00:00')
            ->timezone(getTimezone())
            ->appendOutputTo(storage_path('logs/pk-event-winner.log'))
            ->runInBackground();

        $schedule->command('pk-event-update')
            ->dailyAt('00:00')
            ->timezone(getTimezone())
            ->appendOutputTo(storage_path('logs/pk-event-update.log'))
            ->runInBackground();


        $schedule->command('app:update-game-wallet')
            ->monthlyOn(1, '00:00')
            ->timezone('UTC')
            ->appendOutputTo(storage_path('logs/app-update-game-wallet.log'))
            ->runInBackground();

        $schedule->command('users:update-salaries')
            ->everyTenMinutes()
            ->timezone(getTimezone())
            ->appendOutputTo(storage_path('logs/update-user-salaries.log'))
            ->runInBackground();


        // $schedule->command('game:user-calc')
        //     ->monthly()
        //     ->timezone(getTimezone())
        //     ->appendOutputTo(storage_path('logs/game-user-calc.log'))
        //     ->runInBackground();


        $schedule->command('app:update-gift-rankings')
            ->everyThirtySeconds()
            ->runInBackground();

        $schedule->command('weekly-cp-winner')
            ->weekly()
            ->timezone(getTimezone())
            ->appendOutputTo(storage_path('logs/weekly-cp-winner.log'))
            ->runInBackground();

        $schedule->command('coin_game:archive')
            ->dailyAt('07:00')
            ->timezone(getTimezone())
            ->withoutOverlapping()
            ->runInBackground();
        $schedule->command('coin-game:aggregate')
        ->dailyAt('07:00')
        ->timezone(getTimezone())
        ->withoutOverlapping()
        ->runInBackground();
    
        // $schedule->command('users:freeze-unfinished')
        //     ->everySecond()
        //     ->timezone(getTimezone())
        //     ->appendOutputTo(storage_path('logs/stop-transfer-salary.log'))
        //     ->runInBackground();

       /*$schedule->command('log:app-profit-coins')->everyTenMinutes();

        $schedule->job(new AgencyTribeRewardJob())
            ->daily()->when(function (){
                $startDate = Carbon::create(2025, 1, 1);
                $today = Carbon::today();

                return $startDate->diffInDays($today) % 15 === 0;
            });

        $schedule->job(new CleanExpiredAgencyRewardsJob())->daily();*/
        //    $schedule->command('log:app-profit-coins')->everyTenMinutes();

    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
