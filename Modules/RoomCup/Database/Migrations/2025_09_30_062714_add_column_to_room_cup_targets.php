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
        Schema::table('room_cup_targets', function (Blueprint $table) {
            $table->decimal('total_profit', 8, 2)->default(0);
            $table->integer('owner_percentage')->default(0);
            $table->integer('admin_percentage')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_cup_targets', function (Blueprint $table) {
            $table->dropColumn('total_profit');
            $table->dropColumn('owner_percentage');
            $table->dropColumn('admin_percentage');
        });
    }
};
