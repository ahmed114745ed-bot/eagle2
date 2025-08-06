<?php

namespace Modules\CP\Http\Services;

use App\Helpers\Common;
use App\Helpers\UserCommon;
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
use Modules\CP\Entities\Cp as EntitiesCp;
use Modules\CP\Entities\CpLevel;
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
                    $cpIds[$receiver->id] = $cpId;
                }
            }
        }

        return $cpIds;
    }

    protected function processGiftForReceiver(User $sender, User $receiver, int $giftId, int $giftPrice)
    {
        $checkIfExistCp = EntitiesCp::where(function ($query) use ($sender, $receiver) {
            $query->where('user_one_id', $sender->id)
                ->where('user_two_id', $receiver->id)
                ->orWhere(function ($query) use ($sender, $receiver) {
                    $query->where('user_two_id', $sender->id)
                        ->where('user_one_id', $receiver->id);
                });
        })
            ->whereIn('status', [1, 4])
            ->whereHas('cpRelation', function ($q) {
                $q->where('type', '!=', 'solution');
            })
            ->first();

        if (!$checkIfExistCp) {
            return false;
        }

        $this->upgradeLevelAndExp($checkIfExistCp, $giftPrice);
        return $checkIfExistCp->id;
    }

    /**
     * @throws \Throwable
     */
    public function upgradeLevelAndExp(?object $cp, $diamonds = null): bool
    {
        if (!$cp) return false;

        $newDi = $cp->di + $diamonds;
        $level = $this->getLevel($cp->cp_relation_id, $newDi);
        if ($level) {
            DB::table('cps')->where('id', $cp->id)->update([
                'di' => $newDi,
                'level_id' => $level->id
            ]);
            if ($level->level) $this->assignGifts($level, $cp);
        } else {
            DB::table('cps')->where('id', $cp->id)->update([
                'di' => $newDi
            ]);
        }

        return true;
    }

    public function getLevel(int $cpRelationId, int $totalCoins)
    {
        return CpLevel::query()->where('cp_relation_id', $cpRelationId)->where('exp', '<=', $totalCoins)->orderByDesc('exp')->limit(1)->first();
    }



    //////////////////////////////////////////// assign gift ///////////////////////////////////////////////////////////////////

    /**
     * @throws \Throwable
     */
    protected function assignGifts($level, $cp)
    {
        // Check if the CP has already taken the gift for the level
        if ($this->hasTakenGift($cp->id, $level->level)) {
            return true;
        }

        // Fetch rewards for the specified level
        $rewards = $this->getRewardsForLevel($level->id);
        if (!$rewards) return true;
        // Define users associated with the CP
        $userOne = $this->getUserById($cp->user_one_id);
        $userTwo = $this->getUserById($cp->user_two_id);

        // Distribute rewards to the users
        $this->distributeRewards($rewards, $userOne, $userTwo);

        // Mark the gift as taken for the CP and level
        $this->markGiftAsTaken($cp->id, $level->level);
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
        return CpLevelGift::whereHas('cp_level', function ($q) use ($level) {
            $q->where('id', $level);
        })->get();
    }

    /**
     * @throws \Throwable
     */
    protected function distributeRewards($rewards, $userOne, $userTwo)
    {
        foreach ($rewards as $reward) {
            switch ($reward->type) {
                case 'coins':
                    $this->assignCoins($reward, $userOne, $userTwo);
                    break;
                case 'vip':
                    $this->assignVip($reward, $reward->expire, $userOne, $userTwo);
                    break;
                case 'ware':
                    $ware = Ware::find($reward->item_id);
                    if ($ware) $this->assignWare($ware, $reward, $userOne, $userTwo);
                    break;
                case 'achievement':
                    $this->assignAchievement($reward, $reward->expire, $userOne, $userTwo);
                    break;
            }
        }
    }

    protected function assignCoins($reward, $userOne, $userTwo): void
    {
        $userOneGender = $userOne->profile->gender == 1 ? 'male' : 'female';
        $userTwoGender = $userTwo->profile->gender == 1 ? 'male' : 'female';
        $vipGender = $reward->gender;
        $amount = $reward->item_id;

        if ($amount) {
            if ($vipGender == $userOneGender || $vipGender == 'all'){
                $userOne->increment('di', $amount);
            }
            if ($vipGender == $userTwoGender || $vipGender == 'all') {
                $userTwo->increment('di', $amount);
            }
        }
    }

    /**
     * @throws \Throwable
     */
    protected function assignVip($reward, $expire, $userOne, $userTwo)
    {
        $userOneGender = $userOne->profile->gender == 1 ? 'male' : 'female';
        $userTwoGender = $userTwo->profile->gender == 1 ? 'male' : 'female';
        $vipGender = $reward->gender;
        $vipId = $reward->item_id;
        $vip = OVip::find($vipId);
        if ($vip) {
            if ($vipGender == $userOneGender || $vipGender == 'all'){
                UserCommon::addVipToCpUser($userOne, $vip, $expire);
            }
            if ($vipGender == $userTwoGender || $vipGender == 'all') {
                UserCommon::addVipToCpUser($userTwo, $vip, $expire);
            }
        }
    }

    protected function assignWare($ware, $reward, $userOne, $userTwo)
    {
        // Assign the ware based on gender requirements
        $this->genderForReward($ware, $reward, $userOne, $userTwo);
    }

    protected function assignAchievement($reward, $expire, $userOne, $userTwo)
    {
        $userOneGender = $userOne->profile->gender == 1 ? 'male' : 'female';
        $userTwoGender = $userTwo->profile->gender == 1 ? 'male' : 'female';
        $vipGender = $reward->gender;
        $itemId = $reward->item_id;

        // Handle different formats of $expire
        if (is_numeric($expire)) {
            // Assume it's a timestamp (seconds or milliseconds)
            if ($expire > 9999999999) {
                // Milliseconds -> convert to seconds
                $expire = (int) ($expire / 1000);
            }
            $dateTimestamp = Carbon::createFromTimestamp($expire)->format('Y-m-d H:i:s');
        } elseif (strtotime($expire)) {
            // It's a valid date string
            $dateTimestamp = Carbon::parse($expire)->format('Y-m-d H:i:s');
        } else {
            // Invalid date format fallback
            throw new \InvalidArgumentException("Invalid expire value: $expire");
        }

        $attributes = [
            'custom_image' => $itemId,
            'end_at'       => $dateTimestamp,
        ];

        if ($vipGender == $userOneGender || $vipGender == 'all'){
            UserAchievementLevel::create(array_merge($attributes, ['user_id' => $userOne->id]));
        }
        if ($vipGender == $userTwoGender || $vipGender == 'all') {
            UserAchievementLevel::create(array_merge($attributes, ['user_id' => $userTwo->id]));
        }
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
