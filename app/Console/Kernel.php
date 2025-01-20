<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\UpdateRoomUserNowCron::class,
        Commands\OpenStatusAppFeature::class,
        Commands\CloseStatusAppFeature::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('queue:work --queue=default')->withoutOverlapping()->runInBackground();
        $schedule->command('queue:work --queue=updatePkAndSendToZigo')->withoutOverlapping()->runInBackground();
        $schedule->command('queue:work --queue=heavy1')->withoutOverlapping()->runInBackground();
        $schedule->command('queue:work --queue=heavy2')->withoutOverlapping()->runInBackground();
        $schedule->command('queue:work --queue=heavy3')->withoutOverlapping()->runInBackground();
        $days = DB::table('configs')->where( 'name','period_target')->value('value'); 
        if ($days) {
            $schedule->command('schedule:cron')->withoutOverlapping()->runInBackground()->cron("0 0 */{$days} * *");
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Get the timezone that should be used by default for scheduled events.
     *
     * @return \DateTimeZone|string|null
    */
    protected function scheduleTimezone()
    {
        return 'Africa/Cairo';
    }
}
