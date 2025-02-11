<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Agency;
use App\Models\GiftLog;
use App\Models\ExchangeLog;

use App\Models\AgencySallary;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GiftRoomSeeder  extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $giftLogs = GiftLog::take(3)->get();

        foreach ($giftLogs as $giftLog) {
            $giftLog->receiver_id = 828;
            $giftLog->save();
        }


        $giftLogs = GiftLog::take(3)->orderByDesc('id')->get();

        foreach ($giftLogs as $giftLog) {
            $giftLog->sender_id = 828;
            $giftLog->save();
        }

        $agency = Agency::where("id", 45)->first();
        $agency->update(['app_owner_id', 828]);

        $agencySalaries = AgencySallary::take(3)->get();
        foreach ($agencySalaries as $key => $agencySalary) {
            $agencySalary->agency_id = $agency->id;
            $agencySalary->month = $key + 1;
            $agencySalary->year = 2024;
            $agencySalary->save();
        }


        $exchangeLogs = ExchangeLog::take(3)->get();

        foreach ($exchangeLogs as $exchangeLog) {
            $exchangeLog->user_id = 828;
            $exchangeLog->save();
        }

        $room = Room::find(961);

        $giftLogs = GiftLog::take(3)->whereHas('gift')->get();

        foreach ($giftLogs as $giftLog) {
            $giftLog->roomowner_id = $room->id;
            $giftLog->sender_id = 828;
            $giftLog->giftPrice = 165478;
            $giftLog->save();
        }
        
    }
}
