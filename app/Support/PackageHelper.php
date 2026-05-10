<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Utd\Achievements\Entities\Achievement;
use Utd\Chat\Entities\ChatMessage;
use Utd\DailyPrize\Entities\DailyGift;
use Utd\Events\Entities\WeeklyStar;
use Utd\Family\Entities\Family;
use Utd\Gifts\Entities\GiftLog;
use Utd\RankingReward\Entities\RankingReward;
use Utd\RoleRewards\Entities\RoleReward;
use Utd\Tasks\Entities\Day;
use Utd\TaskStream\Entities\TaskStream;
use Utd\Charizma\Entities\ExtraDataInRoom;
use Utd\CP\Entities\Cp;
use Utd\LuckyBox\Entities\Box;
use Utd\Moments\Entities\Moment;
use Utd\Reals\Entities\Real;
use Utd\Pk\Entities\Pk;
use Utd\Room\Entities\Room;
use Utd\RoomBoom\Entities\RoomBoom;
use Utd\RoomCup\Entities\RoomCupTarget;
use Utd\Vip\Entities\OVip;
use Utd\HostLevel\Entities\HostLevel;
use Utd\Badge\Entities\Badge;
use Utd\Form\Entities\FormTemplate;
use Utd\Bd\Entities\Bd;
use Utd\UsersWallet\Entities\UserWallet;
use Utd\Milestones\Entities\Milestone;
use Utd\SpecialId\Entities\SpecialHistory;
use Utd\SwitchAccount\Entities\UserAccount;

class PackageHelper
{
    private static array $packages = [
        'achievement' => Achievement::class,
        'moment' => Moment::class,
        'real' => Real::class,
        'room' => Room::class,
        'pk' => Pk::class,
        'taskStream' => TaskStream::class,
        'roomBoom' => RoomBoom::class,
        'roomCup' => RoomCupTarget::class,
        'charisma' => ExtraDataInRoom::class,
        'cp' => Cp::class,
        'luckyBox' => Box::class,
        'chat' => ChatMessage::class,
        'vip' => OVip::class,
        'family' => Family::class,
        'event' => WeeklyStar::class,
        'task' => Day::class,
        'gift' => GiftLog::class,
        'hostLevel' => HostLevel::class,
        'rankingReward' => RankingReward::class,
        'dailyPrize' => DailyGift::class,
        'specialId' => SpecialHistory::class,
        'switchAccount' => UserAccount::class,
        'RoleReward' => RoleReward::class,
        'milestone' => Milestone::class,
        'badge' => Badge::class,
        'form' => FormTemplate::class,
        'usersWallet' => UserWallet::class,
        'bd' => Bd::class,
    ];

    /**
     * Check if a package is installed
     */
    public static function isInstalled(string $package): bool
    {
        return isset(self::$packages[$package]) && class_exists(self::$packages[$package]);
    }

    /**
     * Get model class if package installed
     */
    public static function getEntity(string $package): ?string
    {
        if (!self::isInstalled($package)) {
            return null;
        }

        return self::$packages[$package] ?? null;
    }

    /**
     * Query model safely
     */
    public static function checkRelation(Model $model, string $package, string $relationType)
    {
        if (!self::isInstalled($package)) {
            return $model->$relationType($model::class, 'id', 'id')->whereRaw('1 = 0');
        }

        return null;
    }
}
