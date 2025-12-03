<?php

namespace Modules\HostLevel\Http\Services;

use Carbon\Carbon;
use App\Models\Ware;
use App\Helpers\Common;
use App\Models\GiftLog;
use App\Helpers\UserCommon;
use App\Enums\UserCoinLogType;
use Modules\Vip\Entities\OVip;
use App\Helpers\UserCoinLogHelper;
use Modules\Events\Entities\GeneralRole;
use Modules\HostLevel\Entities\HostLevel;
use Modules\HostLevel\Entities\HostLevelWinner;
use Modules\Achievement\Entities\UserAchievementLevel;

class HostLevelService
{


    public function hostLevelIndex()
    {
        return HostLevel::with('rewards')->orderBy('level', 'asc')->paginate(15);
    }

    public function roles()
    {
        return GeneralRole::where('type', 'host_level')->first();
    }


    public function pickHostLevel($user, $HistLevelId)
    {

        $userId = $user->id;

        $eventType = $this->getEventType();

        $checkPick = HostLevelWinner::where('user_id', $userId)->where('host_level_id', $HistLevelId)->filterByEventType($eventType)->first();
        if ($checkPick) throw new \Exception(__('you have already picked this host level before'));
        $hostLevel = $this->hostLevel($HistLevelId);
        if (!$hostLevel) throw new \Exception(__('host level not found'));
        $diamonds = $this->computeDiamonds($userId);
        if (!$diamonds || ($diamonds < $hostLevel->diamonds_required)) {
            throw new \Exception(__('you do not meet the diamond requirement to pick this host level'));
        }

        HostLevelWinner::create(
            [
                'user_id' => $userId,
                'host_level_id' => $HistLevelId,
            ]
        );
        if (!$hostLevel->rewards)  return true;


        $this->assignReward($user, $hostLevel->rewards);

        return true;
    }


    public function hostLevel($id)
    {
        return HostLevel::with('rewards')->find($id);
    }

    public function assignReward($user, $rewards)
    {
        foreach ($rewards as $reward) {
            if ($reward->type == "coins") {

                $amountBefore = $user->di;
                UserCoinLogHelper::logByType(
                    $user->id,
                    $reward->target,
                    $amountBefore,
                    UserCoinLogType::HOST_LEVEL,
                );

                $user->di += $reward->target;
                $user->save();
            } elseif ($reward->type == "vip") {
                $vip = OVip::query()->find($reward->target);
                UserCommon::addVipToUser($user, $vip, $reward->expire, null, 'charge-event');
            } elseif ($reward->type == "ware") {
                $ware = Ware::query()->find($reward->target);
                UserCommon::addEvintsWareToUser($user, $ware, $reward->expire, null, 'charge-event');
            } elseif ($reward->type == "achievement") {
                $attributes = [
                    'user_id'       => $user->id,
                    'custom_image' => $reward->target,
                ];
                UserAchievementLevel::create($attributes);
            } elseif ($reward->type == 'badge') {
                Common::userBadge($user->id, $reward->target, $reward->expire, 'charge-event');
            }
        }
    }


    public function nextLevel($user)
    {
        $eventType = $this->getEventType();
        $lastPick = $user->lastHostLevelWinnerByEvent($eventType)->first();
        if ($lastPick && $lastPick->hostLevel) {
            $nextLevel = HostLevel::where('level', '>', $lastPick->hostLevel->level)
                ->orderBy('level', 'asc')
                ->first();
        } else {
            $nextLevel = HostLevel::orderBy('level', 'asc')->first();
        }

        $diamonds = $this->computeDiamonds($user->id);

        if ($nextLevel  && $lastPick) {
            $remaining = $nextLevel->diamonds - $diamonds;
            $exactlyValue    = @$nextLevel->diamonds;
            $progressNext    = $nextLevel->diamonds - $lastPick->diamonds;
            $progressCurrent = $diamonds - $lastPick->diamonds;
            $prog = $progressNext != 0 ? ($progressCurrent / $progressNext) : 0;

            if ($prog >= 1) {
                $bar = 1;
            } else {
                $bar = round($prog, 1);
            }
            $progress = $exactlyValue == 0 ? 1 : $bar;
        } elseif ($lastPick) {

            $exactlyValue    = @$nextLevel->diamonds;
            $progressNext    = @$nextLevel->diamonds - $lastPick->diamonds;
            $progressCurrent = $diamonds - $lastPick->diamonds;
            $prog = $progressNext != 0 ? ($progressCurrent / $progressNext) : 0;

            $progress  = 1;
            $remaining = 0;
        } else {
            $progress  = 1;
            $remaining = 0;
        }
         dd($diamonds);
        $hostLevels = HostLevel::with('rewards')
            ->where('diamonds', '<=', $diamonds)
            ->orderBy('level', 'asc')
            ->get();
      
        return [
            'next' => [
                'next_level' => $nextLevel->level ?? 0,
                'next_level_image' => $nextLevel->img ?? '',
                'diamonds' => $diamonds,
                'remaining' => $remaining < 0 ? 0 : $remaining,
                'progress' => $progress,
            ],

            'levels' => $hostLevels,
        ];
    }

    private function getEventType(): string
    {
        return Common::getSettingValue('host_level_type') ?? 'daily';
    }

    private function computeDiamonds($userId)
    {
        $eventType = $this->getEventType();

        return  GiftLog::where('receiver_id', $userId)
            ->filterByEventType($eventType)
            ->selectRaw('receiver_id, SUM(giftNum * giftPrice) AS total_diamond')->groupBy("receiver_id")
            ->value('total_diamond');
    }
}
