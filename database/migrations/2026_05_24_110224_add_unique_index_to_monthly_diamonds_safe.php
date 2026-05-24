<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add unique index to monthly_diamond_receives with safety check
     * Also merges duplicate records for month 5, year 2026
     */
    public function up(): void
    {
        // Check if the unique index already exists
        $indexExists = $this->indexExists('monthly_diamond_receives', 'idx_user_month_year');

        if ($indexExists) {
            $this->command->warn('⚠️ Unique index "idx_user_month_year" already exists - skipping all operations');
            return;
        }

        // Step 1: Merge duplicates for month 5, year 2026
        $this->command->info('📊 Step 1: Checking for duplicates in month 5, year 2026...');
        $duplicatesCount = $this->mergeDuplicatesForMonth(5, 2026);

        if ($duplicatesCount > 0) {
            $this->command->info("✅ Merged {$duplicatesCount} duplicate groups for May 2026");
        } else {
            $this->command->info('✅ No duplicates found for May 2026');
        }

        // Step 2: Add unique index
        $this->command->info('📊 Step 2: Adding unique index...');

        try {
            Schema::table('monthly_diamond_receives', function (Blueprint $table) {
                $table->unique(['user_id', 'month', 'year'], 'idx_user_month_year');
            });

            $this->command->info('✅ Unique index "idx_user_month_year" added successfully');
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $this->command->error('❌ ERROR: Still have duplicates in other months!');
                $this->command->error('Please run: php artisan migrate:rollback --step=1');
                $this->command->error('Then use the fix route: /fix-monthly-diamonds');
                throw $e;
            }
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if the unique index exists before dropping
        $indexExists = $this->indexExists('monthly_diamond_receives', 'idx_user_month_year');

        if ($indexExists) {
            Schema::table('monthly_diamond_receives', function (Blueprint $table) {
                $table->dropUnique('idx_user_month_year');
            });

            $this->command->info('✅ Unique index "idx_user_month_year" dropped successfully');
        } else {
            $this->command->warn('⚠️ Unique index "idx_user_month_year" does not exist - skipping');
        }
    }

    /**
     * Merge duplicate records for a specific month/year
     *
     * @param int $month
     * @param int $year
     * @return int Number of duplicate groups merged
     */
    private function mergeDuplicatesForMonth(int $month, int $year): int
    {
        // Find all duplicates for this month/year
        $duplicates = DB::table('monthly_diamond_receives')
            ->select('user_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(monthly_diamond_received) as total'))
            ->where('month', $month)
            ->where('year', $year)
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            return 0;
        }

        $mergedCount = 0;

        DB::transaction(function () use ($duplicates, $month, $year, &$mergedCount) {
            foreach ($duplicates as $duplicate) {
                // Get all records for this user/month/year
                $records = DB::table('monthly_diamond_receives')
                    ->where('user_id', $duplicate->user_id)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->orderBy('id')
                    ->get();

                if ($records->count() <= 1) {
                    continue;
                }

                // Calculate totals
                $totalDiamonds = $records->sum('monthly_diamond_received');
                $latestRecord = $records->sortByDesc('updated_at')->first();
                $oldestCreatedAt = $records->min('created_at');

                // Delete all except the latest
                DB::table('monthly_diamond_receives')
                    ->where('user_id', $duplicate->user_id)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->where('id', '!=', $latestRecord->id)
                    ->delete();

                // Update the remaining record with correct total
                DB::table('monthly_diamond_receives')
                    ->where('id', $latestRecord->id)
                    ->update([
                        'monthly_diamond_received' => $totalDiamonds,
                        'created_at' => $oldestCreatedAt,
                        'updated_at' => now(),
                    ]);

                $mergedCount++;
            }
        });

        return $mergedCount;
    }

    /**
     * Check if an index exists on a table
     *
     * @param string $table
     * @param string $indexName
     * @return bool
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return !empty($indexes);
    }
};
