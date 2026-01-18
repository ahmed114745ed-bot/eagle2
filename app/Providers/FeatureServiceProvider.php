<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\AchievementContract;
use App\Services\Null\NullAchievementService;

class FeatureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Achievement Feature
        if (!$this->app->bound(AchievementContract::class)) {
            // Package didn't bind it = use Null implementation
            $this->app->singleton(
                AchievementContract::class,
                NullAchievementService::class
            );
        }
    }
}
