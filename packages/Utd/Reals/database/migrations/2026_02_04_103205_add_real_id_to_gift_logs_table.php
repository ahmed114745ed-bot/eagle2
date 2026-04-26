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
        if (Schema::hasTable('gift_logs') && ! Schema::hasColumn('gift_logs', 'real_id')) {
            Schema::table('gift_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('real_id')->nullable()->after('room_id');
                $table->index('real_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('gift_logs') && Schema::hasColumn('gift_logs', 'real_id')) {
            Schema::table('gift_logs', function (Blueprint $table) {
                $table->dropIndex(['real_id']);
                $table->dropColumn('real_id');
            });
        }
    }
};
