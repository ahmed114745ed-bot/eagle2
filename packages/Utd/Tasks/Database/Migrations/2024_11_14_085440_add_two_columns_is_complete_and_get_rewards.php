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
        if (! Schema::hasColumn('user_days_tasks_progress', 'is_collect')) {
            Schema::table('user_days_tasks_progress', function (Blueprint $table) {
                $table->boolean('is_collect')->default(false);
            });
        }

        if (! Schema::hasColumn('user_day_progress', 'get_rewards')) {
            Schema::table('user_day_progress', function (Blueprint $table) {
                $table->boolean('get_rewards')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_days_tasks_progress', function (Blueprint $table) {
            $table->dropColumn('is_collect');
        });

        Schema::table('user_day_progress', function (Blueprint $table) {
            $table->dropColumn('get_rewards');
        });
        //
    }
};
