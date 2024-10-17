<?php

namespace Modules\CP\Http\Services;

use App\Helpers\Common;
use App\Helpers\UserCommon;
use App\Models\Cp;
use App\Models\OVip;
use App\Models\User;
use App\Models\Vip;
use App\Models\Ware;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Achievement\Entities\AchievementLevel;
use Modules\Achievement\Entities\UserAchievement;
use Modules\Achievement\Entities\UserAchievementLevel;
use Modules\Achievement\Enums\TargetType;
use Modules\CP\Entities\CpLevelGift;
use Modules\CP\Entities\CpLevelTakeGift;

class CpService
{
    public function processCpWhenSendGift(User $sender, $receivers, int $giftId, int $giftPrice)
    {
        $cpIds = [];

        if (count($receivers) > 0) {
            foreach ($receivers as $receiver) {
                $cpId = $this->processGiftForReceiver($sender, $receiver, $giftId, $giftPrice);
                if ($cpId) {
                    $cpIds[] = $cpId;
                }
            }
        }

        return $cpIds;
    }

    protected function processGiftForReceiver(User $sender, User $receiver, int $giftId, int $giftPrice)
    {
        $checkIfExistCp = DB::table('cps')
            ->where(function ($query) use ($sender, $receiver) {
                $query->where('user_one_id', $sender->id)
                    ->where('user_two_id', $receiver->id)
                    ->orWhere(function ($query) use ($sender, $receiver) {
                        $query->where('user_two_id', $sender->id)
                                ->where('user_one_id', $receiver->id);
                    });
            })
            ->whereIn('status', [1, 4])
            ->first();

        if (!$checkIfExistCp) {
            return false;
        }

        $this->upgradeLevelAndExp($checkIfExistCp, $giftPrice);
        return $checkIfExistCp->id;
    }

    public function upgradeLevelAndExp(?object $cp, $diamonds = null): bool
    {
        if (!$cp) return false;

        $newDi = $cp->di + $diamonds;
        $level = $this->getLevel($newDi);
        if ($level) {
            DB::table('cps')->where('id', $cp->id)->update([
                'di' => $newDi,
                'level_id' => $level->level
            ]);
            // $this->assignGifts($level->level, $cp);
        } else {
            DB::table('cps')->where('id', $cp->id)->update([
                'di' => $newDi
            ]);
        }

        return true;
    }

    public function getLevel(int $totalCoins)
    {
        return Vip::query()->where(['type' => 3])->where('exp', '<=', $totalCoins)->orderByDesc('exp')->limit(1)->first();
    }

 
    
    //////////////////////////////////////////// assign gift ///////////////////////////////////////////////////////////////////
    protected function assignGifts($level, $cp)
    {
        // Check if the CP has already taken the gift for the level
        if ($this->hasTakenGift($cp->id, $level)) {
            return true;
        }

        // Fetch rewards for the specified level
        $rewards = $this->getRewardsForLevel($level);

        // Define users associated with the CP
        $userOne = $this->getUserById($cp->user_one_id);
        $userTwo = $this->getUserById($cp->user_two_id);

        // Distribute rewards to the users
        $this->distributeRewards($rewards, $userOne, $userTwo);

        // Mark the gift as taken for the CP and level
        $this->markGiftAsTaken($cp->id, $level);
    }

    protected function getUserById($id) 
    {
       return User::find($id);   
    }

    protected function hasTakenGift($cpId, $level)
    {
        return CpLevelTakeGift::where(['cp_id' => $cpId, 'level' => $level])->exists();
    }

    protected function getRewardsForLevel($level)
    {
        return CpLevelGift::whereHas('vip', function($q) use ($level) {
            $q->where('level', $level);
        })->get();
    }

    protected function distributeRewards($rewards, $userOne, $userTwo)
    {
        foreach ($rewards as $reward) {
            switch ($reward->type) {
                case 'coins':
                    $this->assignCoins($reward->item_id, $userOne, $userTwo);
                    break;
                case 'vip':
                    $this->assignVip($reward->item_id, $reward->expire, $userOne, $userTwo);
                    break;
                case 'ware':
                    $ware = Ware::find($reward->item_id);
                    $this->assignWare($ware, $reward, $userOne, $userTwo);
                    break;
                case 'achievement':
                    $this->assignAchievement($reward->item_id, $reward->expire, $userOne, $userTwo);
                    break;
            }
        }
    }

    protected function assignCoins($amount, $userOne, $userTwo)
    {
        $userOne->increment('di', $amount);
        $userTwo->increment('di', $amount);
    }

    protected function assignVip($vipId, $expire, $userOne, $userTwo)
    {
        $vip = OVip::find($vipId);
        UserCommon::addVipToUser($userOne, $vip, $expire);
        UserCommon::addVipToUser($userTwo, $vip, $expire);
    }

    protected function assignWare($ware, $reward, $userOne, $userTwo)
    {
        // Assign the ware based on gender requirements
        $this->genderForReward($ware, $reward, $userOne, $userTwo);
    }

    protected function assignAchievement($itemId, $expire, $userOne, $userTwo)
    {
        $dateTimestamp = Carbon::parse($expire)->format('Y-m-d H:i:s');

        $attributes = [
            'custom_image' => $itemId,
            'end_at'       => $dateTimestamp,
        ];

        UserAchievementLevel::create(array_merge($attributes, ['user_id' => $userOne->id]));
        UserAchievementLevel::create(array_merge($attributes, ['user_id' => $userTwo->id]));
    }

    protected function genderForReward($ware, $reward, $userOne, $userTwo)
    {
        if ($reward->gender === 'male') {
            $this->assignWareToUserByGender($ware, $reward->expire, $userOne, 1);
            $this->assignWareToUserByGender($ware, $reward->expire, $userTwo, 1);
        } elseif ($reward->gender === 'female') {
            $this->assignWareToUserByGender($ware, $reward->expire, $userOne, 2);
            $this->assignWareToUserByGender($ware, $reward->expire, $userTwo, 2);
        } else {
            UserCommon::addWareToUser($userOne, $ware, $reward->expire);
            UserCommon::addWareToUser($userTwo, $ware, $reward->expire);
        }
    }

    protected function assignWareToUserByGender($ware, $expire, $user, $gender)
    {
        if ($user->profile?->gender === $gender) {
            UserCommon::addWareToUser($user, $ware, $expire);
        }
    }

    protected function markGiftAsTaken($cpId, $level)
    {
        CpLevelTakeGift::create([
            'cp_id' => $cpId,
            'level' => $level,
        ]);
    }


}
