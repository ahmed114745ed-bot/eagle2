<?php

namespace App\Console\Commands;

use App\Models\UserCoinLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LogAppProfitCoinsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:app-profit-coins';

    protected $description = 'Log all app_profit_coins from cron-based tables every 10 minutes';

    public function handle()
    {
        $this->info("Start scanning profit tables...");
        $this->info("Start scanning profit tables...");

        $now = Carbon::now();
        $from = $now->copy()->subMinutes(10)->toDateTimeString();
        $to = $now->toDateTimeString();
    
        // 🧩 جدول gift_logs
        $giftLogs = DB::table('gift_logs')
            ->where('app_profit_coins', '!=', 0)
            ->get();
    
        foreach ($giftLogs as $log) {
            UserCoinLog::create([
                'user_id' => $log->sender_id ?? null,
                'type' => 'gift',
                'sub_type' => 'gift_logs',
                'amount' => $log->app_profit_coins,
                'from_date' => $from,
                'to_date' => $to,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('gift_logs')->where('id', $log->id)->update(['app_profit_coins' => 0]);
        }
    
        $games = DB::table('coin_game_users')
            ->where('app_profit_coins', '!=', 0)
            ->get();
    
        foreach ($games as $game) {
            UserCoinLog::create([
                'user_id' => $game->user_id,
                'type' => 'game',
                'sub_type' => 'coin_game_users',
                'amount' => $game->app_profit_coins,
                'from_date' => $from,
                'to_date' => $to,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('coin_game_users')->where('id', $game->id)->update(['app_profit_coins' => 0]);
        }
    
        // 🧩 جدول user_lucky_gifts
        $luckyGifts = DB::table('user_lucky_gifts')
            ->where('app_profit_coins', '!=', 0)
            ->get();
    
        foreach ($luckyGifts as $gift) {
            UserCoinLog::create([
                'user_id' => $gift->user_id,
                'type' => 'lucky',
                'sub_type' => 'user_lucky_gifts',
                'amount' => $gift->app_profit_coins,
                'from_date' => $from,
                'to_date' => $to,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('user_lucky_gifts')->where('id', $gift->id)->update(['app_profit_coins' => 0]);
        }
    
        $this->info("Profit logs completed ✅");
    }
}
