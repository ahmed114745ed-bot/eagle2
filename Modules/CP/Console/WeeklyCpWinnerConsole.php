<?php

namespace Modules\CP\Console;

use Carbon\Carbon;
use App\Models\OVip;
use App\Models\Ware;
use App\Models\GiftLog;
use App\Helpers\UserCommon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Events\Entities\Winner;
use Modules\CP\Entities\WeeklyCpWinner;
use Modules\Events\Entities\WeeklyStar;
use Modules\Achievement\Entities\UserAchievementLevel;

class WeeklyCpWinnerConsole extends Command
{
    protected $signature = 'weekly-cp-winner';

    protected $description = 'Command description';

    public function handle()
    {
        $weeklyCp = WeeklyStar::endToday()->where('type','weekly_cp')
                                 ->with('gifts', 'weeklyCpGifts')
                                 ->latest()
                                 ->first();

        if (!$weeklyCp) {
            return '';
        }

        $giftIds = $weeklyCp->gifts->pluck('id');
        $leaderBoard = GiftLog::whereIn('giftId', $giftIds)->select(DB::raw('sum(giftPrice) as totalGiftNum'), 'cp_id')
        ->groupBy('cp_id')->whereBetween('created_at', [
            $weeklyCp->start_date,
            $weeklyCp->end_date
        ])->whereHas('cps', function ($q) {
            $q->relation();
        })->with('cp')->orderByDesc('totalGiftNum')->take(3)->get();



        foreach ($leaderBoard as $index => $entry) {
            $alreadyWinner = WeeklyCpWinner::where(['weekly_cp_id'=>$weeklyCp->id,'user_one_id'=>$entry->cp->user_one_id,'user_two_id'=>$entry->cp->user_two_id,'type_relation'=>$entry->cp->relation->title ])->exists();

            if (!$alreadyWinner) {
                $winner = WeeklyCpWinner::create([
                                             'weekly_cp_id' => $weeklyCp->id,
                                             'user_one_id' => $entry->cp->user_one_id,
                                             'user_two_id' => $entry->cp->user_two_id,
                                             'type_relation' =>$entry->cp->relation->title,
                                             'level' =>$index + 1,
                                             'total_price' => $entry->totalGiftNum,
                                         ]);
                $rewardIds = $weeklyCp->weeklyCpGifts->where('level',$index + 1);
                if (count($rewardIds) > 0){
                    foreach ($rewardIds as $reward){
                        if ($reward->type == "coins"){
                            $entry->cp->fromUser->di+=$reward->target;
                            $entry->cp->fromUser->save();
                            $entry->cp->toUser->di+=$reward->target;
                            $entry->cp->toUser->save();

                        }elseif ($reward->type == "vip"){
                            $vip=OVip::query()->find($reward->target);
                            UserCommon::addVipToUser($entry->cp->fromUser,$vip,$reward->expire);
                            UserCommon::addVipToUser($entry->cp->toUser,$vip,$reward->expire);

                        }elseif ($reward->type == "ware"){
                            $ware=Ware::query()->find($reward->target);
                            UserCommon::addWareToUser($entry->sender,$ware,$reward->expire);
                        }elseif ($reward->type == "achievement"){
                            $dateTimestamp = Carbon::parse($reward->expire)->format("Y-m-d H:i:s");
                            $attributes = [
                                'user_id'       => $entry->sender_id,
                                'custom_image' => $reward->target,
                                'end_at' => $dateTimestamp,
                            ];

                            UserAchievementLevel::create($attributes);
                        }else{
                            continue;
                        }

                       
                    }
                }

            }
        }
    }
}
