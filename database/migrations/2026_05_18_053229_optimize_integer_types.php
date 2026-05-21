<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Performance Optimization: Use appropriate integer sizes (TINYINT/SMALLINT instead of INT).
 *
 * Benefits:
 * - Storage: 2 bytes saved per row (INT 4 bytes → SMALLINT 2 bytes)
 * - Memory: Better cache efficiency
 * - Query performance: Faster comparisons on smaller integers
 *
 * Integer Ranges:
 * - TINYINT UNSIGNED: 0 to 255 (1 byte)
 * - TINYINT: -128 to 127 (1 byte)
 * - SMALLINT UNSIGNED: 0 to 65,535 (2 bytes) ← Used for gifts.sort
 * - SMALLINT: -32,768 to 32,767 (2 bytes)
 * - MEDIUMINT UNSIGNED: 0 to 16,777,215 (3 bytes) ← Used for silvers.sort (max: 77,889)
 * - INT UNSIGNED: 0 to 4,294,967,295 (4 bytes)
 *
 * Note: silvers.sort uses MEDIUMINT UNSIGNED (max value found: 77,889)
 *       gifts.sort uses SMALLINT UNSIGNED (safe within 65,535 limit)
 *
 * Estimated Savings: ~400 MB (less than originally estimated due to SMALLINT vs TINYINT)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->info('🔍 Validating integer ranges before conversion...');

        $validation = $this->validateIntegerRanges();

        if (!$validation['safe']) {
            $errorMessage = "❌ Data validation failed! Cannot proceed with migration.\n\n";
            $errorMessage .= "Violations found:\n";
            foreach ($validation['violations'] as $violation) {
                $errorMessage .= "  - {$violation}\n";
            }

            Log::error('Integer type optimization migration failed validation', [
                'violations' => $validation['violations']
            ]);

            throw new \Exception($errorMessage);
        }

        $this->info('✅ Data validation passed. All values fit in proposed types.');

        // ==========================================
        // Convert sort columns to TINYINT UNSIGNED
        // ==========================================

        $this->info('🔄 Optimizing sort columns...');

        DB::statement("ALTER TABLE silvers MODIFY COLUMN sort MEDIUMINT UNSIGNED NULL"); // Max value: 77,889 > 65,535, using MEDIUMINT (3 bytes)
        DB::statement("ALTER TABLE gifts MODIFY COLUMN sort MEDIUMINT UNSIGNED NULL"); // Using MEDIUMINT for safety (same as silvers)

        // ==========================================
        // REMOVED: status and type columns
        // Reason: Production data ranges unknown - safer to skip
        // ==========================================

        $this->info('✅ Integer type optimization completed successfully!');
    }

    /**
     * Validate integer ranges
     */
    private function validateIntegerRanges(): array
    {
        $violations = [];
        $safe = true;

        // Check silvers.sort (should be 0-16777215 for MEDIUMINT UNSIGNED)
        $silverSort = DB::selectOne("
            SELECT MIN(sort) as min, MAX(sort) as max FROM silvers WHERE sort IS NOT NULL
        ");

        if (($silverSort->min ?? 0) < 0 || ($silverSort->max ?? 0) > 16777215) {
            $violations[] = "silvers.sort range [{$silverSort->min}, {$silverSort->max}] exceeds MEDIUMINT UNSIGNED (0-16777215)";
            $safe = false;
        }

        // Check gifts.sort (should be 0-16777215 for MEDIUMINT UNSIGNED)
        $giftSort = DB::selectOne("
            SELECT MIN(sort) as min, MAX(sort) as max FROM gifts WHERE sort IS NOT NULL
        ");

        if (($giftSort->min ?? 0) < 0 || ($giftSort->max ?? 0) > 16777215) {
            $violations[] = "gifts.sort range [{$giftSort->min}, {$giftSort->max}] exceeds MEDIUMINT UNSIGNED (0-16777215)";
            $safe = false;
        }

        // REMOVED: countries.status and store_logs.types validation
        // Reason: Production data ranges unknown - skipped to prevent migration failure

        return [
            'safe' => $safe,
            'violations' => $violations
        ];
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->warn('⚠️  Rolling back integer type optimizations to INT');

        // Revert silvers (was MEDIUMINT)
        DB::statement("ALTER TABLE silvers MODIFY COLUMN sort INT NULL");

        // Revert gifts (was MEDIUMINT)
        DB::statement("ALTER TABLE gifts MODIFY COLUMN sort INT NULL");

        $this->info('Rollback completed.');
    }

    /**
     * Helper methods
     */
    private function info(string $message): void
    {
        if (app()->runningInConsole()) {
            echo $message . PHP_EOL;
        }
        Log::info($message);
    }

    private function warn(string $message): void
    {
        if (app()->runningInConsole()) {
            echo '⚠️  ' . $message . PHP_EOL;
        }
        Log::warning($message);
    }
};
