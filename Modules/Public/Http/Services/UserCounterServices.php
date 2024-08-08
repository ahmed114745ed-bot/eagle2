<?php

namespace Modules\Public\Http\Services;

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Models\Vip;
use App\Models\OVip;
use App\Models\Pack;
use App\Models\User;
use App\Models\Ware;
use App\Models\Banner;
use App\Models\Config;
use App\Models\Follow;
use App\Helpers\Common;
use App\Helpers\UserCommon;
use App\Models\EarnedDiamond;
use App\Models\ProfileVisitor;
use App\Models\OfficialMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Public\Entities\UserCounter;
use Modules\Public\Entities\levelInterval;
use Modules\Public\Jobs\RewardWinnerLevel;
use App\Classes\Gifts\UpdateUserWhenSendGift;
use Modules\Public\Events\UnreadCounterGroup;
use Modules\Public\Entities\RewardLevelInterval;
use Modules\Public\Entities\WinnerLevelInterval;
use Modules\Public\Events\UnreadCounterIndividual;
use Modules\Achievement\Entities\UserAchievementLevel;

class UserCounterServices
{
    public function UpgradeDateForType(User $user, $type = null)
    {
        // $data = UserCounter::updateOrCreate([
        //     "user_id" => $user->id,
        //     "type" => $type,
        // ], [
        //     "date" => $date ?? now()
        // ]);
        $check = UserCounter::where('user_id', $user->id)->where('type', $type)->exists();
        if ($check) {
            $data = UserCounter::where('user_id', $user->id)->where('type', $type)->update(["date" => now()]);
        } else {
            $data = UserCounter::create([
                "user_id" => $user->id,
                "type" => $type,
                "date" => now()
            ]);
        }
      //  \Log::info('data '. $data);
        return $data;
    }

    public function getUserCounts(User $user, $type = null)
    {
        $counters = UserCounter::select("id", 'type', 'date','user_id')
            ->where("user_id", $user->id)
            ->when($type, function ($query) use ($type) {
                $query->where("type", $type);
            })
            ->get();
         //   \Log::info('UserCounter '. $counters);
        $results = 0;

        foreach ($counters as $counter) {
            $count = $this->getCountByType($user, $counter->type, $counter->date);
            $results = $count;
        }

        return $results;
    }

    /**
     * Get the count of specified type.
     *
     * @param User $user
     * @param string $type
     * @param string $date
     * @return int
     */
    private function getCountByType(User $user, string $type, string $date)
    {
        switch ($type) {
            case "system_message":
                return OfficialMessage::where("type", 1)
                    ->where("created_at", ">", $date)
                    ->count();
            case "official_message":
                return OfficialMessage::where("type", 2)
                    ->where("created_at", ">", $date)
                    ->count();
            case "followers":
                return Follow::where("followed_user_id", $user->id)
                    ->where("created_at", ">", $date)
                    ->count();
            case "followeds":
                return Follow::where("user_id", $user->id)
                    ->where("created_at", ">", $date)
                    ->count();
            case "friend":
                return Follow::where(function ($query) use ($user) {
                    $query->where("user_id", $user->id)
                        ->orWhere("followed_user_id", $user->id);
                })
                    ->where("status", 1)
                    ->where("created_at", ">", $date)
                    ->count();
            case "visitor":
                return ProfileVisitor::where("user_id", $user->id)
                    ->where("created_at", ">", $date)
                    ->count();
            case "mybag":
                return Pack::whereUserId($user->id)
                    ->where("created_at", ">", $date)
                    ->count();
            case "mall":
             $ware =   Ware::query()->where("created_at", ">", $date)->where('enable',1)
                ->whereIn ('get_type',[4,6])->count();
                // \Log::info('ware '. $ware,);
                // \Log::info('date'.  $date);
                return $ware;
            default:
                return 0;
        }
    }

    public function eventUser(User $user, $type = null, $counter = null)
    {
       // Log::info('this fire pusher');
       try{
        event(new UnreadCounterIndividual($type, $user, $counter));
    } catch (\Throwable $th) {
        return $th->getMessage();
     }
    }

    public function eventUsers($type = null, $counter = null)
    {
        try{
        event(new UnreadCounterGroup($type, $counter));
    } catch (\Throwable $th) {
        return $th->getMessage();
     }
    }
}
