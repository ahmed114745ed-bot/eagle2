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
                    foreach ($rewardIds as $rewad){
                        if ($rewad->type == "coins"){
                            $entry->cp->fromUser->di+=$rewad->target;
                            $entry->cp->fromUser->save();
                            $entry->cp->toUser->di+=$rewad->target;
                            $entry->cp->toUser->save();

                        }elseif ($rewad->type == "vip"){
                            $vip=OVip::query()->find($rewad->target);
                            UserCommon::addVipToUser($entry->cp->fromUser,$vip,$rewad->expire);
                            UserCommon::addVipToUser($entry->cp->toUser,$vip,$rewad->expire);

                        }elseif ($rewad->type == "ware"){
                            $ware=Ware::query()->find($rewad->target);
                            UserCommon::addWareToUser($entry->sender,$ware,$rewad->expire);
                        }elseif ($rewad->type == "achievement"){
                            $dateTimestamp = Carbon::parse($rewad->expire)->format("Y-m-d H:i:s");
                            $attributes = [
                                'user_id'       => $entry->sender_id,
                                'custom_image' => $rewad->target,
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
