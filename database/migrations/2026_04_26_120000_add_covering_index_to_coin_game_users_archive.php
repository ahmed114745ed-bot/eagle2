<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!$this->indexExists('coin_game_users_archive', 'cgu_archive_covering_index')) {
            Schema::table('coin_game_users_archive', function (Blueprint $table) {
                $table->index(['game_id', 'user_id', 'type', 'coins', 'created_at'], 'cgu_archive_covering_index');
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $result = $connection->selectOne(
            "SELECT COUNT(*) as count FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$database, $table, $index]
        );

        return $result->count > 0;
    }

    public function down(): void
    {
        if ($this->indexExists('coin_game_users_archive', 'cgu_archive_covering_index')) {
            Schema::table('coin_game_users_archive', function (Blueprint $table) {
                $table->dropIndex('cgu_archive_covering_index');
            });
        }
    }
};
