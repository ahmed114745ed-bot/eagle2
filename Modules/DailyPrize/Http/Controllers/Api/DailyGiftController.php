<?php

namespace Modules\DailyPrize\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\OVip;
use App\Models\Ware;
use App\Helpers\Common;
use App\Helpers\UserCommon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Support\Renderable;
use Modules\Achievement\Entities\Achievement;
use Modules\DailyPrize\Entities\DailyUserGift;
use Modules\DailyPrize\Entities\DailyGiftCount;
use Modules\Achievement\Entities\UserAchievement;
use Modules\DailyPrize\Transformers\WeeklyStarGift;
use Modules\Achievement\Entities\UserAchievementLevel;
use Modules\DailyPrize\Http\Services\DailyPrizeService;
use Modules\Achievement\Http\Services\AchievementService;
use Modules\Achievement\Transformers\AchievementResource;
use Modules\Achievement\Transformers\AchievementDetailResource;
use Modules\Achievement\Transformers\AchievementOneLevelsResource;

class DailyGiftController extends Controller
{

    public function __construct(private DailyPrizeService $dailyPrizeService) { }

    public function current_day()
    {
        $user = Auth::user();
       
        //reset daily
       
        (new DailyPrizeService())->reset(Carbon::createFromTimestamp($user->real_online_time), $user->id);
       
        $currentDay= $this->getCurrentDay();
        
        $result=DailyGiftCount::query()->where('user_id',$user->id)->first();
       
        $check_received=DailyGiftCount::query()->where('user_id',$user->id)->where("day_count",$currentDay)->first();
        $data=[
            'current_day'   => ($currentDay % 7 == 0 ? 7 : $currentDay % 7),
            'gift'          => $this->getGift($currentDay) ??[],
            'is_received'   => $check_received != null ? true : false,
        ];
        return Common::apiResponse(1, '', $data);
    }

    public function receive_daily_prize()
    {
        $user = Auth::user();
        $currentDay= $this->getCurrentDay();

        $dailyGift=$this->dailyPrizeService->getDayGift($currentDay);
        if (!$dailyGift) {
            return Common::apiResponse(0, 'يوجد شئ ما خطا', []);
        }
        $result=DailyGiftCount::query()->where('user_id',$user->id)->orderByDesc('id')->first();
        if (!$this->dailyPrizeService->isNewDay($user->id) && $result != null) {
            return Common::apiResponse(0, 'لم يمر 24 ساعه لاستلام الهديه التاليه', []);
        }

        $type = $dailyGift->gift_type;
        $target = $dailyGift->target;
        $expire = $dailyGift->expir;
        DailyGiftCount::query()->updateOrCreate([
            'user_id' => $user->id,

        ], ['last_active' => now(),
            'day_count' => $currentDay,

            ]);
        DailyUserGift::create([
            'user_id'   => $user->id,
            'gift_type' =>$type,
            'target'    =>$target,
        ]);
        $this->assignGiftToUser($type, $user, $target, $expire);
        return Common::apiResponse(1, 'تم استلام الجائزه بنجاح', []);
    }

    public function getCurrentDay()
    {
        $user = Auth::user();
        $result=DailyGiftCount::query()->where('user_id',$user->id)->first();
        $currentDay = 1;
        if ($result != null) {
            $currentDay = $result->day_count;
            if ($this->dailyPrizeService->isNewDay($user->id)){
                $currentDay += 1;
            }
        }


        return $currentDay;
    }
    public function assignGiftToUser(mixed $type, \App\Models\Admin|\Illuminate\Contracts\Auth\Authenticatable|null $user, mixed $target, mixed $expire): void
    {
        if ($type == "coins") {
            $user->di += $target;
            $user->save();
        } elseif ($type == "vip") {
            $vip = OVip::query()->find($target);
            UserCommon::addVipToUser($user, $vip, $expire);
        } elseif ($type == "ware") {
            $ware = Ware::query()->find($target);
            UserCommon::addWareToUser($user, $ware, $expire);
        } elseif ($type == "achievement") {
            $attributes = [
                'user_id'      => $user->id,
                'custom_image' => $target,
                'end_at' =>  Carbon::parse($expire)->format("Y-m-d H:i:s"),
            ];
            UserAchievementLevel::create($attributes);
        }
    }

    public function check_date_hours($date)
    {
        $specificDateTime = new \DateTime($date);
        $now = new \DateTime();
        $diff = $now->diff($specificDateTime);
        $hoursDifference = ($diff->days * 24) + $diff->h + ($diff->i / 60) + ($diff->s / 3600);
        if ($hoursDifference > 24){
            return false;
        }
        return true;
    }

    public function getGift($currentDay)
    {

        $data=$this->dailyPrizeService->getDayGift($currentDay);
        return  $data!=null? new WeeklyStarGift($data) :[];
    }
}
