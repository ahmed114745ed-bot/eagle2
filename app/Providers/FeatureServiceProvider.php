<?php
namespace App\Providers;

use App\Contracts\MomentContract;
use App\Contracts\AchievementContract;
use App\Contracts\AchievementLevelContract;
use App\Contracts\UserAchievementContract;
use App\Services\Null\NullMomentService;
use App\Services\Null\NullAchievementService;
use App\Services\Null\NullAchievementLevelService;
use App\Services\Null\NullUserAchievementService;
use Illuminate\Support\ServiceProvider;

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

        if (!$this->app->bound(AchievementLevelContract::class)) {
            $this->app->singleton(
                AchievementLevelContract::class,
                NullAchievementLevelService::class
            );
        }

        if (!$this->app->bound(UserAchievementContract::class)) {
            $this->app->singleton(
                UserAchievementContract::class,
                NullUserAchievementService::class
            );
        }

        // Moment Feature
        if (!$this->app->bound(MomentContract::class)) {
            $this->app->singleton(
                MomentContract::class,
                NullMomentService::class
            );
        }
    }
}
