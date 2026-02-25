<?php

namespace Utd\Events;

use App\Contracts\LoseWinnerRewardsContract;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Utd\Events\Console\WeeklyStarUpdate;
use Utd\Events\Console\WeeklyStarWinner;
use Utd\Events\Services\LoseWinnerRewards;

class EventsServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/events');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'events');
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'events');
            $this->loadJsonTranslationsFrom(__DIR__.'/../Resources/lang');
        }
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/events');
        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', 'events-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), 'events');

        $componentNamespace = 'Utd\\Events\\View\\Components';
        Blade::componentNamespace($componentNamespace, 'events');
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->bind(LoseWinnerRewardsContract::class, LoseWinnerRewards::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
            WeeklyStarWinner::class,
            WeeklyStarUpdate::class,
        ]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('events.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'events');
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (Config::get('view.paths') as $path) {
            if (is_dir($path.'/modules/events')) {
                $paths[] = $path.'/modules/events';
            }
        }

        return $paths;
    }
}
