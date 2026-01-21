<?php

namespace App\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

trait SafeMigration
{
    /**
     * Create table only if it doesn't exist
     */
    protected function createTableIfNotExists(string $table, callable $callback): void
    {
        if (!Schema::hasTable($table)) {
            Schema::create($table, $callback);
        }
    }

    /**
     * Add column only if it doesn't exist
     */
    protected function addColumnIfNotExists(string $table, string $column, callable $callback): void
    {
        if (Schema::hasTable($table) && !Schema::hasColumn($table, $column)) {
            Schema::table($table, $callback);
        }
    }

    /**
     * Add multiple columns only if they don't exist
     */
    protected function addColumnsIfNotExist(string $table, array $columns, callable $callback): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $missingColumns = array_filter($columns, fn($col) => !Schema::hasColumn($table, $col));
        
        if (!empty($missingColumns)) {
            Schema::table($table, $callback);
        }
    }

    /**
     * Rename table safely
     */
    protected function renameTableIfPossible(string $from, string $to): void
    {
        if (Schema::hasTable($from) && !Schema::hasTable($to)) {
            Schema::rename($from, $to);
        }
    }

    /**
     * Rename column safely
     */
    protected function renameColumnIfPossible(string $table, string $from, string $to): void
    {
        if (Schema::hasTable($table) && Schema::hasColumn($table, $from) && !Schema::hasColumn($table, $to)) {
            Schema::table($table, function (Blueprint $table) use ($from, $to) {
                $table->renameColumn($from, $to);
            });
        }
    }

    /**
     * Drop column only if it exists
     */
    protected function dropColumnIfExists(string $table, string $column): void
    {
        if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->dropColumn($column);
            });
        }
    }

    /**
     * Drop table only if it exists
     */
    protected function dropTableIfExists(string $table): void
    {
        Schema::dropIfExists($table);
    }
}
