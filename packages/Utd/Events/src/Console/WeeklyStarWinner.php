<?php

namespace Utd\Events\Console;

use App\Enums\UserCoinLogType;
use App\Helpers\Common;
use App\Helpers\UserCoinLogHelper;
use App\Helpers\UserCommon;
use App\Models\GiftLog;
use App\Models\Ware;
use App\Support\PackageHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Utd\Achievements\Entities\UserAchievementLevel;
use Utd\Events\Entities\WeeklyStar;
use Utd\Events\Entities\Winner;
use Utd\Vip\Entities\OVip;

class WeeklyStarWinner extends Command
{
    protected $signature = 'weekly-star-winner';

    protected $description = 'Command description';

    public function handle()
    {
        $weeklyEvent = WeeklyStar::dayEnd()->where('type', 'weekly_star')
            ->with('gifts', 'rewards')
            ->latest()
            ->first();

        if (! $weeklyEvent) {
            return '';
        }

        $giftIds = $weeklyEvent->gifts->pluck('id');
        $leaderboard = GiftLog::whereIn('giftId', $giftIds)
            ->whereBetween('created_at', [$weeklyEvent->start_date, Carbon::parse($weeklyEvent->end_date)->endOfDay()])
            ->with('sender')
            ->select(DB::raw('SUM(giftPrice) AS total_gift_num'), 'sender_id')
            ->groupBy('sender_id')
            ->orderByDesc('total_gift_num')
            ->take(3)
            ->get();

        foreach ($leaderboard as $index => $entry) {
            $alreadyWinner = Winner::where([
                'weekly_star_id' => $weeklyEvent->id,
                'user_id' => $entry->sender_id,
            ])->exists();

            if (! $alreadyWinner) {
                $winner = Winner::create([
                    'weekly_star_id' => $weeklyEvent->id,
                    'user_id' => $entry->sender_id,
                    'level' => $index + 1,
                ]);
                $rewardIds = $weeklyEvent->rewards->where('level', $index + 1);
                if (count($rewardIds) > 0) {
                    foreach ($rewardIds as $reward) {

                        $expiredAt = now()->addDays($reward->expire);
                        if ($reward->type === 'coins') {

                            $amountBefore = $entry?->sender?->di;
                            UserCoinLogHelper::logByType(
                                $entry->sender?->id,
                                $reward->target,
                                $amountBefore,
                                UserCoinLogType::WEEKLY_STAR,
                            );

                            $entry->sender->di += $reward->target;
                            $entry->sender->save();
                        } elseif ($reward->type === 'vip') {
                            $vip = null;
                            if (PackageHelper::isInstalled('vip')) {
                                $vip = OVip::query()->find($reward->target);
                            }
                            if ($vip) {
                                UserCommon::addVipToUser($entry->sender, $vip, $reward->expire, null, 'weekly-star');
                            }
                        } elseif ($reward->type === 'ware') {
                            $ware = Ware::query()->find($reward->target);
                            UserCommon::addWareToUser($entry->sender, $ware, $reward->expire, null, 'weekly-star');
                        } elseif ($reward->type === 'achievement') {
                            if (class_exists(UserAchievementLevel::class)) {
                                // $dateTimestamp = Carbon::parse($reward->expire)->format("Y-m-d H:i:s");
                                $dateTimestamp = optional(Carbon::make($reward->expire))->format('Y-m-d H:i:s');
                                $attributes = [
                                    'user_id' => $entry->sender_id,
                                    'custom_image' => $reward->target,
                                    'end_at' => $dateTimestamp,
                                ];

                                UserAchievementLevel::create($attributes);
                            }
                        } elseif ($reward->type === 'badge') {
                            Common::userBadge($entry->sender_id, $reward->target, $reward->expire, 'weekly-star');
                        } else {
                            continue;
                        }

                        $data = [
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
        //        $this->info(now()->toDateTimeString() . ' '. $this->signature . ' Run successful...');
    }
}
