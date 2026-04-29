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
        if (Schema::hasTable('custom_zego_messages') && ! Schema::hasColumn('custom_zego_messages', 'gift_id')) {
            Schema::table('custom_zego_messages', function (Blueprint $table) {
                $table->bigInteger('gift_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('custom_zego_messages') && Schema::hasColumn('custom_zego_messages', 'gift_id')) {
            Schema::table('custom_zego_messages', function (Blueprint $table) {
                $table->dropColumn('gift_id');
            });
        }
    }
};
