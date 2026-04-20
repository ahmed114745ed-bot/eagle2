<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds 'total' column to gift_logs if it does not already exist.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('gift_logs', 'total')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            Schema::table('gift_logs', function (Blueprint $table) {
                $table->bigInteger('total')->nullable()->comment('total');
            });
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('gift_logs', 'total')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            Schema::table('gift_logs', function (Blueprint $table) {
                $table->dropColumn('total');
            });
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
};
