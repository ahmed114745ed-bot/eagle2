<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\UpdateRoomUserNowCron::class,
        Commands\OpenStatusAppFeature::class,
        Commands\CloseStatusAppFeature::class,
        Commands\DeleteTrashedUsers::class,
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
            ->timezone(getTimezone())
            ->appendOutputTo(storage_path('logs/redis-get-data.log'))
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
            ->timezone(getTimezone())
            ->appendOutputTo(storage_path('logs/app-update-game-wallet.log'))
            ->runInBackground();

        $schedule->command('users:update-salaries')
            ->everyTenMinutes()
            ->timezone(getTimezone())
            ->appendOutputTo(storage_path('logs/update-user-salaries.log'))
            ->runInBackground();

//        $schedule->command('log:app-profit-coins')->everyTenMinutes();

    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
