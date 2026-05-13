<?php

namespace App\Console;

use Carbon\Carbon;
use App\Helpers\Common;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Modules\TaskStream\Jobs\PkSessionJob;
use Illuminate\Console\Scheduling\Schedule;
use Modules\CP\Console\WeeklyCpWinnerConsole;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
            ->withoutOverlapping()
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
            ->withoutOverlapping()
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
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/weekly-star-winner.log'))
            ->runInBackground();

        $schedule->command('weekly-star-update')
            ->dailyAt('00:00')
            ->timezone(getTimezone())
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/weekly-star-update.log'))
            ->runInBackground();

        $schedule->command('pk-event-winner')
            ->dailyAt('00:00')
            ->timezone(getTimezone())
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/pk-event-winner.log'))
            ->runInBackground();

        $schedule->command('pk-event-update')
            ->dailyAt('00:00')
            ->timezone(getTimezone())
            ->withoutOverlapping()
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

        $this->scheduleRoomCupRewards($schedule);


        $schedule->command('users:update-offline')
            ->everyThirtyMinutes()
            ->timezone(getTimezone())
            ->withoutOverlapping()
            ->runInBackground();


        // $schedule->command('roomcup:calculate-rewards')->dailyAt('23:59');

        $schedule->call(function () {
            dispatch(new PkSessionJob());
        })
            ->name('pk-session-job')
            ->everyMinute()
            ->timezone(getTimezone())
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/pk_session_job.log'));

        $schedule->command('remaining-diamonds')
            ->monthly()
            ->timezone(getTimezone())
            ->appendOutputTo(storage_path('logs/remaining-diamonds.log'))
            ->runInBackground();

        $schedule->command('monthly-ranking')
            ->monthly()
            ->timezone(getTimezone())
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/monthly-ranking.log'))
            ->runInBackground();

        $schedule->command('daily-ranking')
            ->dailyAt('00:00')
            ->timezone(getTimezone())
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/daily-ranking.log'))
            ->runInBackground();
        $weekEnd = Common::getSettingValue('week_start') ?? 'monday';

        // Convert string to Carbon constant
        $carbonDay = constant('Carbon\\Carbon::' . strtoupper($weekEnd));
        $schedule->command('weekly-ranking')
            ->weeklyOn($carbonDay, '00:00')
            ->timezone(getTimezone())
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/weekly-ranking.log'))
            ->runInBackground();

        $schedule->command('fairluck:sync-wallets')
            ->everyMinute()
            ->appendOutputTo(storage_path('logs/fairluck-sync-wallets.log'))
            ->runInBackground();

        // Performance: Cleanup old fair_luck data (30 days retention)
     /*   $schedule->command('cleanup:fair-luck-transactions --days=30 --chunk=5000')
            ->dailyAt('03:00')
            ->timezone(getTimezone())
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/cleanup-fair-luck-transactions.log'))
            ->runInBackground();

        $schedule->command('cleanup:fair-luck-wallet-histories --days=30 --chunk=5000')
            ->dailyAt('04:00')
            ->timezone(getTimezone())
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/cleanup-fair-luck-wallet-histories.log'))
            ->runInBackground();*/
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }

    private function scheduleRoomCupRewards(Schedule $schedule): void
    {
        $settings = $this->getRoomCupSettings();

        if (empty($settings['enabled'])) {
            return;
        }

        $type = $settings['type'] ?? 'daily';
        $time = '00:00';

        $weekStartDay = \App\helper\TimeHelper::startOfWeekConst();

        $command = $schedule->command('roomcup:calculate-rewards')
            ->timezone(getTimezone());

        match ($type) {
            'daily' => $command->dailyAt($time),
            'weekly' => $command->weeklyOn($weekStartDay, $time),
            'monthly' => $command->monthlyOn(1, $time),
            default => $command->dailyAt($time),
        };
    }

    private function getRoomCupSettings(): array
    {

        $default = [
            'enabled' => true,
            'interval_minutes' => 60,
            'type' => 'daily',
            'time' => '00:00',
        ];

        $settings = [];

        foreach ($default as $key => $defaultValue) {
            $cacheKey = 'roomcup_' . $key;

            $value = Cache::get($cacheKey);

            if ($value === null) {
                $setting = Setting::where('key', $cacheKey)->first();
                $value = $setting ? $setting->value : $defaultValue;

                Cache::put($cacheKey, $value, now()->addDays(30));
            }

            if ($key === 'enabled') {
                $value = (bool) $value;
            } elseif ($key === 'interval_minutes') {
                $value = (int) $value;
            }

            $settings[$key] = $value;
        }

        return $settings;
    }
}
