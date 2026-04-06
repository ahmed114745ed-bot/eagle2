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
        Schema::table('vips', function (Blueprint $table) {
            // Add unique constraint on (type, exp) to prevent duplicate exp values per type
            $table->unique(['type', 'exp'], 'vips_type_exp_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vips', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique('vips_type_exp_unique');
        });
    }
};
