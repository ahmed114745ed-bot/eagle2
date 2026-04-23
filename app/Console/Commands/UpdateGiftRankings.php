<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateGiftRankings extends Command
{

    protected $signature = 'app:update-gift-rankings';

    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!settings()->get('gift_send')) return;

        settings()->set('gift_send', false);

        $timezone = getTimezone();

        //'Africa/Cairo'
        $periods = [
            'daily' => \Carbon\Carbon::now($timezone)->startOfDay()->copy()->setTimezone('UTC'),
            'weekly' => \Carbon\Carbon::now($timezone)->startOfWeek()->startOfDay()->copy()->setTimezone('UTC'),
            'monthly' => \Carbon\Carbon::now($timezone)->startOfMonth()->startOfDay()->copy()->setTimezone('UTC'),
        ];

        foreach ($periods as $type => $startDate) {
            $this->updateRanking($type, 'sender', 'sender_id', $startDate);
            $this->updateRanking($type, 'receiver', 'receiver_id', $startDate);
            $this->updateRanking($type, 'roomOwner', 'roomowner_id', $startDate);
            $this->updateRanking($type, 'agency', 'agency_id', $startDate);
            $this->updateRanking($type, 'roomId', 'room_id', $startDate);
        }
    }


    private function updateRanking(string $type, string $role, string $column, $startDate): void
    {
        switch ($role) {
            case 'agency':
                $rankerType = \App\Models\Agency::class;
                break;

            case 'roomId':
                $rankerType = \App\Models\Room::class;
                break;
            default:
                $rankerType = \App\Models\User::class;
                break;
        }

        $rankings = DB::connection()->getPdo()->prepare("
            SELECT
                $column AS ranker_id,
                SUM(giftPrice) AS total_gifts
            FROM gift_logs
            WHERE created_at >= ?
            AND $column IS NOT NULL
            AND $column != 0
            GROUP BY $column
            ORDER BY total_gifts DESC
            LIMIT 100
        ");
        
        $rankings->execute([$startDate]);
        $rankingData = $rankings->fetchAll(\PDO::FETCH_ASSOC);

        // Prepare insert data outside of transaction
        $now = now();
        $insertData = array_map(function ($ranking) use ($type, $role, $rankerType, $now) {
            return [
                'type' => $type,
                'role' => $role,
                'ranker_id' => $ranking['ranker_id'],
                'ranker_type' => $rankerType,
                'total_gifts' => $ranking['total_gifts'],
                'last_calculated_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $rankingData);

        DB::transaction(function () use ($type, $role, $insertData) {
            DB::table('gift_rankings')
                ->where('type', $type)
                ->where('role', $role)
                ->delete();

            // Insert in chunks to avoid memory issues with large datasets
            foreach (array_chunk($insertData, 50) as $chunk) {
                DB::table('gift_rankings')->insert($chunk);
            }
        });
    }
}
