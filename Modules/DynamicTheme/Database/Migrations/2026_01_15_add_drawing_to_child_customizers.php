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
        Schema::table('child_customizers', function (Blueprint $table) {
            // Add drawing columns
            $table->longText('drawing_data')->nullable()->comment('Drawing image data (base64)');
            $table->json('drawing_metadata')->nullable()->comment('Drawing metadata (width, height, steps, etc)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('child_customizers', function (Blueprint $table) {
            $table->dropColumn(['drawing_data', 'drawing_metadata']);
        });
    }
};
