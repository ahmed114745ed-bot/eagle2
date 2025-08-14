<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Follow;
use App\Models\GiftLog;
use Illuminate\Database\Seeder;
use Modules\CP\Entities\Cp;

class CpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $giftLogs = GiftLog::take(100)->get();
        CP::query()->update([
            'status' => 1,
            'cp_relation_id' => 12
        ]);
        $Cps = Cp::get();

        foreach ($giftLogs as $index => $giftLog) {
            $cp = $Cps[$index % $Cps->count()] ?? null;

            if ($cp) {
                $giftLog->update([
                    'cp_id' => $cp->id,
                    'created_at' => now(),
                    'sender_id' => $cp->user_one_id,
                    'receiver_id' => $cp->user_two_id, // assuming this exists
                ]);
            }
        }
    }
}
