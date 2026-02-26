<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fair_luck_transactions', function (Blueprint $table) {
            $table->decimal('app_fee', 12, 2)->default(0)->after('bet_amount');
            $table->decimal('receiver_fee', 12, 2)->default(0)->after('app_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fair_luck_transactions', function (Blueprint $table) {
            $table->dropColumn(['app_fee', 'receiver_fee']);
        });
    }
};
