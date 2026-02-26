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
        Schema::table('user_luck_profiles', function (Blueprint $table) {
            $table->boolean('is_in_recovery')->default(false);
            $table->decimal('recovery_target_profit', 20, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('user_luck_profiles', function (Blueprint $table) {
            $table->dropColumn(['is_in_recovery', 'recovery_target_profit']);
        });
    }
};
