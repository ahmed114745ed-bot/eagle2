<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArchiveCoinGameUsers extends Command
{
    protected $signature = 'coin_game:archive {year?} {month?}';
    protected $description = 'Archive coin_game_users to coin_game_users_archive at month end';

    public function handle()
    {
        $this->info('Starting archive process...');

        $year = $this->argument('year') ?? Carbon::now()->subMonth()->year;
        $month = $this->argument('month') ?? Carbon::now()->subMonth()->month;

        $ym = (int) ($year . str_pad($month, 2, '0', STR_PAD_LEFT));
        $partitionName = 'p' . $ym;

        $partitions = DB::select("
            SELECT PARTITION_NAME, PARTITION_DESCRIPTION 
            FROM INFORMATION_SCHEMA.PARTITIONS 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'coin_game_users_archive'
        ");
        $partitionExists = collect($partitions)->pluck('PARTITION_NAME')->contains($partitionName);

        if (!$partitionExists) {
            $lastPartition = collect($partitions)
                ->filter(fn($p) => $p->PARTITION_NAME !== 'pMax')
                ->sortBy('PARTITION_DESCRIPTION')
                ->last();

            $newPartitionValue = $ym;
            if ($lastPartition && $lastPartition->PARTITION_DESCRIPTION >= $newPartitionValue) {
                $newPartitionValue = $lastPartition->PARTITION_DESCRIPTION + 1;
            }

            try {
                DB::statement("
                    ALTER TABLE coin_game_users_archive
                    REORGANIZE PARTITION pMax INTO (
                        PARTITION {$partitionName} VALUES LESS THAN ({$newPartitionValue}),
                        PARTITION pMax VALUES LESS THAN MAXVALUE
                    )
                ");
                $this->info("Partition {$partitionName} added successfully.");
            } catch (\Exception $e) {
                $this->error("Failed to add partition {$partitionName}: " . $e->getMessage());
                return;
            }
        } else {
            $this->info("Partition {$partitionName} already exists.");
        }

        DB::beginTransaction();
        try {
            DB::statement("
                INSERT INTO coin_game_users_archive
                (id, user_id, coins, type, game_id, round_id, order_id, app_profit_coins, created_at, updated_at, created_ym)
                SELECT 
                    id, user_id, coins, type, game_id, round_id, order_id, app_profit_coins, created_at, updated_at,
                    YEAR(created_at)*100 + MONTH(created_at)
                FROM coin_game_users
                WHERE MONTH(created_at) = {$month} 
                  AND YEAR(created_at) = {$year}
            ");

            DB::statement("
                DELETE FROM coin_game_users
                WHERE MONTH(created_at) = {$month} 
                  AND YEAR(created_at) = {$year}
            ");

            DB::commit();
            $this->info('Archive completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Archive failed: ' . $e->getMessage());
        }
    }
}
