<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

/**
 * Temporary controller for checking and fixing game order duplicates
 * DELETE THIS FILE after the issue is resolved on production
 */
class GameDuplicateCheckController extends Controller
{
    /**
     * Check duplicate orders status
     * GET /api/game-duplicate-check/status
     */
    public function status(Request $request)
    {
        try {
            // Count duplicates
            $duplicates = DB::select('
                SELECT COUNT(*) as count
                FROM (
                    SELECT order_id, COUNT(*) as cnt
                    FROM coin_game_users
                    WHERE order_id IS NOT NULL
                    GROUP BY order_id
                    HAVING cnt > 1
                ) as dups
            ')[0]->count ?? 0;

            // Total orders
            $totalOrders = DB::table('coin_game_users')->whereNotNull('order_id')->count();
            $uniqueOrders = DB::table('coin_game_users')->whereNotNull('order_id')->distinct('order_id')->count('order_id');

            // Check if unique index exists
            $hasUniqueIndex = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                AND table_name = 'coin_game_users'
                AND index_name = 'unique_order_id'
            ")[0]->count ?? 0;

            // Check if backup table exists
            $hasBackup = DB::select("SHOW TABLES LIKE 'coin_game_users_duplicates_backup'");

            // Get sample duplicates
            $sampleDuplicates = DB::select('
                SELECT order_id, COUNT(*) as count, MIN(created_at) as first, MAX(created_at) as last
                FROM coin_game_users
                WHERE order_id IS NOT NULL
                GROUP BY order_id
                HAVING COUNT(*) > 1
                ORDER BY count DESC
                LIMIT 5
            ');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_orders' => $totalOrders,
                    'unique_orders' => $uniqueOrders,
                    'duplicate_orders' => $duplicates,
                    'duplicate_percentage' => $totalOrders > 0 ? round(($duplicates / $totalOrders) * 100, 4) : 0,
                    'has_unique_index' => $hasUniqueIndex > 0,
                    'has_backup_table' => count($hasBackup) > 0,
                    'backup_count' => count($hasBackup) > 0 ? DB::table('coin_game_users_duplicates_backup')->count() : 0,
                    'sample_duplicates' => $sampleDuplicates,
                    'migrations_pending' => $this->checkPendingMigrations(),
                    'timestamp' => now()->toDateTimeString(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run migrations (cleanup + unique index)
     * POST /api/game-duplicate-check/migrate
     */
    public function runMigrations(Request $request)
    {
        try {
            // Enable the migrations to run
            config(['app.allow_duplicate_cleanup_migration' => true]);

            // Run migrations
            Artisan::call('migrate', [
                '--force' => true,
            ]);

            $output = Artisan::output();

            // Disable after execution
            config(['app.allow_duplicate_cleanup_migration' => false]);

            return response()->json([
                'status' => 'success',
                'message' => 'Migrations executed successfully',
                'output' => $output,
                'timestamp' => now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            // Disable on error too
            config(['app.allow_duplicate_cleanup_migration' => false]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check for pending migrations
     */
    private function checkPendingMigrations()
    {
        try {
            $ran = DB::table('migrations')->pluck('migration')->toArray();

            $pending = [];
            $files = glob(database_path('migrations/*.php'));

            foreach ($files as $file) {
                $migration = basename($file, '.php');
                if (!in_array($migration, $ran)) {
                    $pending[] = $migration;
                }
            }

            return $pending;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get recent logs related to duplicate orders
     * GET /api/game-duplicate-check/logs
     */
    public function logs(Request $request)
    {
        try {
            $logFile = storage_path('logs/laravel.log');

            if (!file_exists($logFile)) {
                return response()->json([
                    'status' => 'success',
                    'logs' => [],
                    'message' => 'Log file not found'
                ]);
            }

            // Get last 100 lines
            $lines = [];
            $file = new \SplFileObject($logFile, 'r');
            $file->seek(PHP_INT_MAX);
            $lastLine = $file->key();
            $start = max(0, $lastLine - 100);

            $file->seek($start);
            while (!$file->eof()) {
                $line = $file->current();
                if (stripos($line, 'Order already') !== false ||
                    stripos($line, 'Duplicate') !== false ||
                    stripos($line, 'Cleanup') !== false) {
                    $lines[] = $line;
                }
                $file->next();
            }

            return response()->json([
                'status' => 'success',
                'logs' => $lines,
                'count' => count($lines),
                'timestamp' => now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
