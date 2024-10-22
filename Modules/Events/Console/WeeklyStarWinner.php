<?php

namespace Modules\Events\Console;

use App\Helpers\UserCommon;
use App\Models\GiftLog;
use App\Models\OVip;
use App\Models\Ware;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Achievement\Entities\UserAchievementLevel;
use Modules\Events\Entities\WeeklyStar;
use Modules\Events\Entities\Winner;

class WeeklyStarWinner extends Command
{
    protected $signature = 'weekly-star-winner';

    protected $description = 'Command description';

    public function handle()
    {
        $weeklyEvent = WeeklyStar::endToday()->where('type','weekly_star')
                                 ->with('gifts', 'rewards')
                                 ->latest()
                                 ->first();

        if (!$weeklyEvent) {
            return '';
        }

        $giftIds = $weeklyEvent->gifts->pluck('id');
        $leaderboard = GiftLog::whereIn('giftId', $giftIds)
                              ->whereBetween('created_at', [$weeklyEvent->start_date, $weeklyEvent->end_date])
                              ->with('sender')
                              ->select(DB::raw('SUM(giftPrice) AS total_gift_num'), 'sender_id')
                              ->groupBy('sender_id')
                              ->orderByDesc('total_gift_num')
                              ->take(3)
                              ->get();



        foreach ($leaderboard as $index => $entry) {
            $alreadyWinner = Winner::where([
                                               'weekly_star_id' => $weeklyEvent->id,
                                               'user_id' => $entry->sender_id
                                           ])->exists();

            if (!$alreadyWinner) {
                $winner = Winner::create([
                                             'weekly_star_id' => $weeklyEvent->id,
                                             'user_id' => $entry->sender_id,
                                             'level' => $index + 1,
                                         ]);
                $rewardIds = $weeklyEvent->rewards->where('level',$index + 1);
                if (count($rewardIds) > 0){
                    foreach ($rewardIds as $reward){

                        $expiredAt = now()->addDays($reward->expire);
                        if ($reward->type == "coins"){
                            $entry->sender->di+=$reward->target;
                            $entry->sender->save();
                        }elseif ($reward->type == "vip"){
                            $vip=OVip::query()->find($reward->target);
                            UserCommon::addVipToUser($entry->sender,$vip,$reward->expire);

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

                        $data= [
                            'winner_id' => $entry->sender_id,
                            'reward_id' => $reward->id,
                            'expaired_at' => $expiredAt,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        DB::table('winner_rewards')->insert($data);
                    }
                }

            }
        }
    }
}
