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
        // Check if table exists first
        if (Schema::hasTable('child_customizers')) {
            Schema::table('child_customizers', function (Blueprint $table) {
                // Add only if columns don't exist
                if (!Schema::hasColumn('child_customizers', 'drawing_data')) {
                    $table->longText('drawing_data')->nullable()->comment('Drawing image data (base64)');
                }
                if (!Schema::hasColumn('child_customizers', 'drawing_metadata')) {
                    $table->json('drawing_metadata')->nullable()->comment('Drawing metadata (width, height, steps, etc)');
                }
            });
        }
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
