<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('user_history_rewards')) {
            Schema::table('user_history_rewards', function (Blueprint $table) {
                // Drop if it already exists
                if (Schema::hasColumn('user_history_rewards', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });

            // Recreate deleted_at column
            Schema::table('user_history_rewards', function (Blueprint $table) {
                $table->softDeletes(); // adds 'deleted_at' column as timestamp nullable
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_history_rewards') && !Schema::hasColumn('user_history_rewards', 'deleted_at')) {
            Schema::table('user_history_rewards', function (Blueprint $table) {
                $table->softDeletes(); // ✅ re-adds if you rollback
            });
        }
    }
};
