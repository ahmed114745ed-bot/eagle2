<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // coin_game_users
        DB::statement("ALTER TABLE coin_game_users ADD INDEX idx_cgu_user_id (user_id)"); 
        DB::statement("ALTER TABLE coin_game_users ADD INDEX idx_cgu_created_at (created_at)");
        DB::statement("ALTER TABLE coin_game_users ADD INDEX idx_cgu_user_created (user_id, created_at)");
        DB::statement("ALTER TABLE coin_game_users ADD INDEX idx_cgu_type (type)");

        // coin_game_users_archive
        DB::statement("ALTER TABLE coin_game_users_archive ADD INDEX idx_cgua_user_id (user_id)");
        DB::statement("ALTER TABLE coin_game_users_archive ADD INDEX idx_cgua_created_at (created_at)");
        DB::statement("ALTER TABLE coin_game_users_archive ADD INDEX idx_cgua_user_created (user_id, created_at)");
        DB::statement("ALTER TABLE coin_game_users_archive ADD INDEX idx_cgua_type (type)");

        // Create view
        DB::statement("
            CREATE OR REPLACE VIEW coin_game_users_merged AS
            SELECT 
                user_id,
                DATE(created_at) as date,
                type,
                SUM(coins) as coins
            FROM coin_game_users
            GROUP BY user_id, DATE(created_at), type

            UNION ALL

            SELECT 
                user_id,
                DATE(created_at) as date,
                type,
                SUM(coins) as coins
            FROM coin_game_users_archive
            GROUP BY user_id, DATE(created_at), type
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS coin_game_users_merged");

        DB::statement("ALTER TABLE coin_game_users DROP INDEX idx_cgu_user_id");
        DB::statement("ALTER TABLE coin_game_users DROP INDEX idx_cgu_created_at");
        DB::statement("ALTER TABLE coin_game_users DROP INDEX idx_cgu_user_created");
        DB::statement("ALTER TABLE coin_game_users DROP INDEX idx_cgu_type");

        DB::statement("ALTER TABLE coin_game_users_archive DROP INDEX idx_cgua_user_id");
        DB::statement("ALTER TABLE coin_game_users_archive DROP INDEX idx_cgua_created_at");
        DB::statement("ALTER TABLE coin_game_users_archive DROP INDEX idx_cgua_user_created");
        DB::statement("ALTER TABLE coin_game_users_archive DROP INDEX idx_cgua_type");
    }
};
