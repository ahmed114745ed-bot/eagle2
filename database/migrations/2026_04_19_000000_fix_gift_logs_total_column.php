<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Cache\LockTimeoutException;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gift_logs', function (Blueprint $table) {
            // Change the 'total' column from TINYINT to BIGINT to support large discrepancy values
            // TINYINT max: 127, BIGINT max: 9,223,372,036,854,775,807
            $table->bigInteger('total')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gift_logs', function (Blueprint $table) {
            // Revert back to TINYINT
            $table->tinyInteger('total')->change();
        });
    }
};
