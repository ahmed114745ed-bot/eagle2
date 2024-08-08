<?php

namespace Modules\Public\Jobs;

use App\Models\Gift;
use App\Models\OVip;
use App\Models\User;
use App\Models\Ware;
use App\Helpers\UserCommon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Public\Entities\LevelInterval;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Public\Entities\RewardLevelInterval;
use Modules\Public\Entities\WinnerLevelInterval;
use Modules\Achievement\Entities\UserAchievementLevel;
use Modules\Achievement\Http\Services\UserAchievementService;

class RewardWinnerLevel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $userId;
    protected $level;
    protected $type;

    public function __construct($userId, $level ,$type)
    {
        $this->userId = $userId;
        $this->level = $level;
        $this->type = $type;
    }

    public function handle()
    {

        $levelInterval = LevelInterval::where('min', '<=', $this->level)->where('type',$this->type)
            ->where('max', '>=', $this->level)
            ->first();


        if ($levelInterval) {
            $rewards = RewardLevelInterval::where('level_interval_id', $levelInterval->id)->get();
            $user = User::query()->find($this->userId);
            foreach ($rewards as $rewad) {

                if ($rewad->type == "coins") {
                    $user->di += $rewad->target;
                    $user->save();
                } elseif ($rewad->type == "vip") {
                    $vip = OVip::query()->find($rewad->target);
                    UserCommon::addVipToUser($user, $vip, $rewad->expire);
                } elseif ($rewad->type == "ware") {
                    $ware = Ware::query()->find($rewad->target);
                    UserCommon::addWareToUser($user, $ware, $rewad->expire);
                } elseif ($rewad->type == "achievement") {
                    $attributes = [
                        'user_id'       => $user->id,
                        'custom_image' => $rewad->target,
                    ];

                    UserAchievementLevel::create($attributes);
                } else {
                    continue;
                }

                $data = [
                    'user_id' => $user->id,
                    'reward_level_interval_id' => $rewad->id,
                    'user_level' => $this->level,
                    'min' => $levelInterval->min,
                    'max' => $levelInterval->max,
                    'type' => $rewad->type,
                    'level_interval_id' => $levelInterval->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                WinnerLevelInterval::query()->create($data);
            }
        }
    }
}
