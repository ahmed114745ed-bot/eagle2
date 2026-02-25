<?php

namespace Utd\CP\Database\Seeders;

use App\Models\GiftLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Utd\CP\Entities\Cp;
use Utd\Events\Entities\WeeklyStar;

class CpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $giftLogs = GiftLog::take(200)->get();
        $giftLogsLast = GiftLog::take(50)->orderByDesc('id')->get();
        Cp::query()->update([
            'status' => 1,
            'cp_relation_id' => 12,
        ]);
        $weeklyCp = WeeklyStar::find(22);

        // Get 20 random users first
        $userIds = User::inRandomOrder()->limit(20)->pluck('id')->toArray();

        // Loop 10 times (because each CP will take 2 users from the 20)
        for ($i = 0; $i < 10; $i++) {
            $userOne = $userIds[$i * 2];
            $userTwo = $userIds[$i * 2 + 1];

            Cp::create([
                'user_one_id' => $userOne,
                'user_two_id' => $userTwo,
                'status' => 1,
                'cp_relation_id' => 12,
            ]);
        }

        $Cps = Cp::get();

        foreach ($giftLogs as $index => $giftLog) {
            $cp = $Cps[$index % $Cps->count()] ?? null;

            if ($cp) {
                $giftLog->update([
                    'cp_id' => $cp->id,
                    'giftId' => 416,
                    'created_at' => now(),
                    'giftNum' => rand(1, 10),
                    'giftPrice' => rand(30000, 100000),
                    'sender_id' => $cp->user_one_id,
                    'receiver_id' => $cp->user_two_id,
                ]);
            }
        }

        foreach ($giftLogsLast as $index => $giftLog) {
            $cp = $Cps[$index % $Cps->count()] ?? null;

            if ($cp) {
                $giftLog->update([
                    'cp_id' => $cp->id,
                    'giftId' => 412,
                    'created_at' => \Carbon\Carbon::parse($weeklyCp->start_date)->toDateString(),
                    'giftNum' => rand(1, 10),
                    'giftPrice' => rand(30000, 100000),
                    'sender_id' => $cp->user_one_id,
                    'receiver_id' => $cp->user_two_id,
                ]);
            }
        }
    }
}
