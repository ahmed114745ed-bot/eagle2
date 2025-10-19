<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class AggregateCoinGameUsers extends Command
{
    protected $signature = 'coin-game:aggregate {--date=}';
    protected $description = 'Aggregate coin game users data daily (current + archive)';

    public function handle(): void
    {
        $date = $this->option('date') ?? Carbon::yesterday()->toDateString();

        $this->info("Aggregating data for date: $date");

        $sql = "
        INSERT INTO coin_game_users_daily_aggregated 
            (user_id, game_id, date, total_played, total_loss, total_win, app_profit, created_at, updated_at)
        SELECT
            user_id,
            game_id,
            DATE(created_at) AS date,
            SUM(coins) AS total_played,
            SUM(CASE WHEN type = 0 THEN coins ELSE 0 END) AS total_loss,
            SUM(CASE WHEN type = 1 THEN coins ELSE 0 END) AS total_win,
            SUM(CASE WHEN type = 0 THEN coins ELSE 0 END) - SUM(CASE WHEN type = 1 THEN coins ELSE 0 END) AS app_profit,
            NOW(),
            NOW()
        FROM (
            SELECT user_id, game_id, coins, type, created_at 
            FROM coin_game_users 
            WHERE DATE(created_at) = ? AND game_id IS NOT NULL
            UNION ALL
            
            SELECT user_id, game_id, coins, type, created_at 
            FROM coin_game_users_archive 
            WHERE DATE(created_at) = ? AND game_id IS NOT NULL
        ) t
        GROUP BY user_id, game_id, DATE(created_at)
        ON DUPLICATE KEY UPDATE
            total_played = VALUES(total_played),
            total_loss = VALUES(total_loss),
            total_win = VALUES(total_win),
            app_profit = VALUES(app_profit),
            updated_at = NOW()
    ";
        \DB::statement($sql, [$date, $date]);

        $this->info("✅ Aggregation complete for date $date");
    }
}
