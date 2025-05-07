<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Agency;
use App\Models\GiftLog;
use App\Models\UserSallary;
use App\Models\UserTarget;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgencyDataSeeder extends Seeder
{
    public function run()
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $agency = Agency::find(43);

        // Update UserSallary and related user
        $userSalaries = UserSallary::with('user')->take(10)->get();
        foreach ($userSalaries as $salary) {
            $salary->update([
                'user_agency_id' => $agency->id,
                'month' => $month,
                'year' => $year,
            ]);

            $salary->user?->update([
                'agency_id' => $agency->id,
            ]);
        }

        // Update GiftLogs and related sender/receiver
        $gifts = GiftLog::with('sender', 'receiver')->take(10)->get();
        foreach ($gifts as $gift) {
            $gift->update([
                'agency_id' => $agency->id,
                'created_at' => now(),
            ]);

            $gift->sender?->update(['agency_id' => $agency->id]);
            $gift->receiver?->update(['agency_id' => $agency->id]);
        }

        // Update UserTargets and related users
        $targets = UserTarget::with('user')->take(20)->get();
        foreach ($targets as $target) {
            $target->update([
                'agency_id' => $agency->id,
                'created_at' => now(),
            ]);

            $target->user?->update(['agency_id' => $agency->id]);
        }
    }
}

