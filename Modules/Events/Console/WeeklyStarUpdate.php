<?php

namespace Modules\Events\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Events\Entities\Reward;
use Modules\Events\Entities\WeeklyStar;

class WeeklyStarUpdate extends Command
{
    protected $signature = 'weekly-star-update';

    protected $description = 'Command description';

    public function handle()
    {
        $weeklyEvent = WeeklyStar::weeklyStar()
                                 ->endToday()
                                 ->with('gifts')
                                 ->first();

        if (!$weeklyEvent) {
            return '';
        }

        $lastEndDate  = WeeklyStar::weeklyStar()->max('end_date');
        $newStartDate = Carbon::parse($lastEndDate)->toDateString();
        $newEndDate   = Carbon::parse($lastEndDate)->addWeek()->toDateString();

        $newWeeklyStar = new WeeklyStar([
                                            'admin_id'   => $weeklyEvent->admin_id,
                                            'start_date' => $newStartDate,
                                            'end_date'   => $newEndDate,
                                            'editor_id'  => $weeklyEvent->editor_id,
                                            'type'       => $weeklyEvent->type,
                                        ]);
        $newWeeklyStar->save();

        $gifts = $weeklyEvent->gifts->map(function ($gift) use ($newWeeklyStar) {
            return [
                'weekly_star_id' => $newWeeklyStar->id,
                'gift_id'        => $gift->id,
            ];
        })->toArray();

        if (!empty($gifts)) {
            DB::table('weekly_star_gifts')->insert($gifts);
        }
        $this->repeatRewards($weeklyEvent, $newWeeklyStar->id);

    }

    public function repeatRewards(WeeklyStar $weeklyStar, int $weeklyStarNewId)
    {
        $columns         = [
            "weekly_star_id",
            "type",
            "level",
            "target",
            "created_at",
            "updated_at",
            "expire",
        ];
        $previousRewards = $weeklyStar->rewards()->get($columns)->toArray();

        $reward  = new Reward;
        $appends = $reward->getAppends();
        foreach ($previousRewards as &$previousReward) {
            $previousReward['weekly_star_id'] = $weeklyStarNewId;
            $previousReward['created_at']     = now();
            $previousReward['updated_at']     = now();

            foreach ($appends as $append) {
                unset($previousReward[$append]);
            }
        }

        Reward::query()->insert($previousRewards);

    }
}
