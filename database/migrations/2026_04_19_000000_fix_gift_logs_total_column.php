<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('gift_logs', 'total')) {
            Schema::table('gift_logs', function (Blueprint $table) {
                $table->bigInteger('total')->nullable()->comment('سعر الهدية الاصلي المرسله');
            });
        } else {
            // Change the 'total' column to BIGINT to support large values
            // Disable FK checks to avoid constraint issues with orphaned rows
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::statement("ALTER TABLE gift_logs CHANGE total total BIGINT DEFAULT NULL COMMENT 'سعر الهدية الاصلي المرسله'");
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
            DB::statement("ALTER TABLE gift_logs CHANGE total total DECIMAL(12,2) UNSIGNED DEFAULT NULL COMMENT 'سعر الهدية الاصلي المرسله'");
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
};
