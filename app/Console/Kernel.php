<?php

namespace App\Console;

use App\Models\Setting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        $schedule->command('normal-lucy-box')->everyMinute()->withoutOverlapping()->runInBackground();
        $schedule->command('super-lucy-box')->everyMinute()->withoutOverlapping()->runInBackground();
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
        $timezone = \Cache::rememberForever('timezone', function () {
            $setting =   Setting::where('key', 'timezone')->first();
            return $setting?->value ?? 'UTC';
        });
        
        return $timezone;
    }
}
