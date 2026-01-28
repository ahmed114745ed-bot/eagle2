<?php

namespace App\Console\Commands;

use App\Helpers\AgencyPackageHelper;
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
            // Only update agency ranking if package is installed
            if (AgencyPackageHelper::isAgencyInstalled()) {
                $this->updateRanking($type, 'agency', 'agency_id', $startDate);
            }
            $this->updateRanking($type, 'roomId', 'room_id', $startDate);
        }
    }


    private function updateRanking(string $type, string $role, string $column, $startDate): void
    {

        switch ($role) {
            case 'agency':
                $rankerType = AgencyPackageHelper::getAgencyClass() ?? \App\Models\Agency::class;
                break;

            case 'roomId':
                $rankerType = \App\Models\Room::class;
                break;
            default:
                $rankerType = \App\Models\User::class;
                break;
        }
        DB::transaction(function () use ($type, $role, $column, $startDate, $rankerType) {
            // 1. Delete all old rankings of this type
            DB::table('gift_rankings')
                ->where('type', $type)
                ->where('role', $role)
                ->delete();

            // 2. Insert fresh rankings
            DB::statement("
            INSERT INTO gift_rankings (
                type, role, ranker_id, ranker_type, total_gifts, last_calculated_at, created_at, updated_at
            )
            SELECT
                " . DB::getPdo()->quote($type) . ",
                " . DB::getPdo()->quote($role) . ",
                $column AS ranker_id,
                '" . addslashes($rankerType) . "' AS ranker_type,
                SUM(giftPrice) AS total_gifts,
                NOW(),
                NOW(),
                NOW()
            FROM gift_logs
            WHERE created_at >= " . DB::getPdo()->quote($startDate) . "
            AND $column IS NOT NULL
            AND $column != 0
            GROUP BY $column
            ORDER BY total_gifts DESC
            LIMIT 100
        ");
        });
    }
}
