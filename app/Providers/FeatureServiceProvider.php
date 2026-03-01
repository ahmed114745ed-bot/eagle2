<?php
namespace App\Providers;

use App\Contracts\CpRepositoryContract;
use App\Contracts\CpServiceContract;
use App\Contracts\EnteranceRoomContract;
use App\Contracts\NewRoomBoomGiftServiceContract;
use App\Contracts\RoomGameContract;
use App\Contracts\RoomServiceContract;
use App\Contracts\RoomRepositoryContract;
use App\Contracts\RoomSalaryRepositoryContract;
use App\Contracts\RoomTopUsersRepositoryContract;
use App\Contracts\RoomVisitorRepositoryContract;
use App\Contracts\MomentContract;
use App\Contracts\AchievementContract;
use App\Contracts\AchievementLevelContract;
use App\Contracts\UserAchievementContract;
use App\Contracts\RealsContract;
use App\Services\Null\NullCpRepository;
use App\Services\Null\NullCpService;
use App\Services\Null\NullEntranceRoomService;
use App\Services\Null\NullNewRoomBoomGiftService;
use App\Contracts\GiftLogRepositoryContract;
use App\Contracts\GiftRepositoryContract;
use App\Contracts\PkRepositoryContract;
use App\Contracts\UserDevicesHistoryRepositoryContract;
use App\Services\Null\NullEnteranceRoomService;
use App\Services\Null\NullUserDevicesHistoryRepository;
use App\Services\Null\NullRoomGameService;
use App\Services\Null\NullRoomService;
use App\Services\Null\NullRoomRepository;
use App\Services\Null\NullRoomSalaryRepository;
use App\Services\Null\NullRoomTopUsersRepository;
use App\Services\Null\NullRoomVisitorRepository;
use App\Services\Null\NullMomentService;
use App\Services\Null\NullAchievementService;
use App\Services\Null\NullAchievementLevelService;
use App\Services\Null\NullUserAchievementService;
use App\Services\Null\NullRealsService;
use App\Services\Null\NullGiftLogRepository;
use App\Services\Null\NullGiftRepository;
use App\Contracts\RoleRewardContract;
use App\Services\Null\NullPkRepository;
use App\Services\Null\NullRoleRewardService;
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

        // Reals Feature
        if (!$this->app->bound(RealsContract::class)) {
            $this->app->singleton(
                RealsContract::class,
                NullRealsService::class
            );
        }

        // Room Enterance Feature
        if (!$this->app->bound(EnteranceRoomContract::class)) {
            $this->app->singleton(
                EnteranceRoomContract::class,
                NullEntranceRoomService::class
            );
        }

        // Room Game Feature
        if (!$this->app->bound(RoomGameContract::class)) {
            $this->app->singleton(
                RoomGameContract::class,
                NullRoomGameService::class
            );
        }

        // Room Service Feature
        if (!$this->app->bound(RoomServiceContract::class)) {
            $this->app->singleton(
                RoomServiceContract::class,
                NullRoomService::class
            );
        }

        // Room Repository Feature
        if (!$this->app->bound(RoomRepositoryContract::class)) {
            $this->app->singleton(
                RoomRepositoryContract::class,
                NullRoomRepository::class
            );
        }

        // Room Salary Repository Feature
        if (!$this->app->bound(RoomSalaryRepositoryContract::class)) {
            $this->app->singleton(
                RoomSalaryRepositoryContract::class,
                NullRoomSalaryRepository::class
            );
        }

        // Room Top Users Repository Feature
        if (!$this->app->bound(RoomTopUsersRepositoryContract::class)) {
            $this->app->singleton(
                RoomTopUsersRepositoryContract::class,
                NullRoomTopUsersRepository::class
            );
        }

        // Room Visitor Repository Feature
        if (!$this->app->bound(RoomVisitorRepositoryContract::class)) {
            $this->app->singleton(
                RoomVisitorRepositoryContract::class,
                NullRoomVisitorRepository::class
            );
        }

        // Room Boom Feature
        if (!$this->app->bound(NewRoomBoomGiftServiceContract::class)) {
            $this->app->singleton(
                NewRoomBoomGiftServiceContract::class,
                NullNewRoomBoomGiftService::class
                   );
        } 
        // Gift Log Repository Feature
        if (!$this->app->bound(GiftLogRepositoryContract::class)) {
            $this->app->singleton(
                GiftLogRepositoryContract::class,
                NullGiftLogRepository::class
            );
        }

        // Gift Repository Feature
        if (!$this->app->bound(GiftRepositoryContract::class)) {
            $this->app->singleton(
                GiftRepositoryContract::class,
                NullGiftRepository::class
            );
        }

        // Pk Repository Feature
        if (!$this->app->bound(PkRepositoryContract::class)) {
            $this->app->singleton(
                PkRepositoryContract::class,
                NullPkRepository::class
            );
        }

        // CP Feature
        if (!$this->app->bound(CpServiceContract::class)) {
            $this->app->singleton(
                CpServiceContract::class,
                NullCpService::class
            );
        }

        // CP Repository Feature
        if (!$this->app->bound(CpRepositoryContract::class)) {
            $this->app->singleton(
                CpRepositoryContract::class,
                NullCpRepository::class
            );
        }

        // Role Reward Feature
        if (!$this->app->bound(RoleRewardContract::class)) {
            $this->app->singleton(
                RoleRewardContract::class,
                NullRoleRewardService::class
            );
        }

        // User Devices History Repository Feature (SwitchAccount)
        if (!$this->app->bound(UserDevicesHistoryRepositoryContract::class)) {
            $this->app->singleton(
                UserDevicesHistoryRepositoryContract::class,
                NullUserDevicesHistoryRepository::class
            );
        }
    }
}
