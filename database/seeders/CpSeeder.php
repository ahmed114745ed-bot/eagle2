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
        $giftLogs = GiftLog::take(28)->get();
        $Cps = Cp::get();

        foreach ($giftLogs as $index => $giftLog) {
            $cp = $Cps[$index % $Cps->count()] ?? null; // Assign each GiftLog a unique Cp
            if ($cp) {
                $giftLog->update([
                    'cp_id' => $cp->id,
                    'created_at' => now()
                ]);
            }
        }
    }
}
