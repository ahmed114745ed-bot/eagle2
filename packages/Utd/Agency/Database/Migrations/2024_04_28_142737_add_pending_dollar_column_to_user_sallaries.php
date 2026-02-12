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
        if (! Schema::hasColumn('user_sallaries', 'pending_dollar')) {
            Schema::table('user_sallaries', function (Blueprint $table) {
                $table->double('pending_dollar')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_sallaries', function (Blueprint $table) {
            $table->dropColumn('pending_dollar');
        });
    }
};
