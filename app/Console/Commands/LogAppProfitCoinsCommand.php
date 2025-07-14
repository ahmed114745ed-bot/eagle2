<?php

namespace App\Console\Commands;

use App\Models\UserCoinLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LogAppProfitCoinsCommand extends Command
{
    
        protected $signature = 'log:app-profit-coins';
        protected $description = 'Log all app_profit_coins from cron-based tables every 10 minutes';
    
        public function handle()
        {
            $this->info("Start scanning profit tables...");
    
            $tables = [
                [
                    'table' => 'gift_logs',
                    'user_column' => 'sender_id',
                    'type' => 'gift',
                    'sub_type' => 'gift_logs'
                ],
                [
                    'table' => 'coin_game_users',
                    'user_column' => 'user_id',
                    'type' => 'game',
                    'sub_type' => 'coin_game_users'
                ],
                [
                    'table' => 'user_lucky_gifts',
                    'user_column' => 'user_id',
                    'type' => 'lucky',
                    'sub_type' => 'user_lucky_gifts'
                ]
            ];
    
            foreach ($tables as $config) {
                $this->processTable(
                    $config['table'],
                    $config['user_column'],
                    $config['type'],
                    $config['sub_type']
                );
            }
    
            $this->info("Profit logs completed ✅");
        }
    
        protected function processTable(string $table, string $userColumn, string $type, string $subType): void
        {
            $rows = DB::table($table)
                ->select('id', $userColumn, 'app_profit_coins', 'created_at')
                ->where('app_profit_coins', '!=', 0)
                ->get();
    
            if ($rows->isEmpty()) return;
    
            $from = $rows->min('created_at');
            $to = $rows->max('created_at');
    
            $now = now();
            $logs = [];
    
            foreach ($rows as $row) {
                $logs[] = [
                    'user_id' => $row->{$userColumn},
                    'type' => $type,
                    'sub_type' => $subType,
                    'amount' => $row->app_profit_coins,
                    'from_date' => $from,
                    'to_date' => $to,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
    
            UserCoinLog::insert($logs);
    
            DB::table($table)
                ->whereIn('id', collect($rows)->pluck('id'))
                ->update(['app_profit_coins' => 0]);
        }
    
}
