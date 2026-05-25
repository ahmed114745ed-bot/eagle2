<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add indexes to optimize /api/search endpoint performance
     * Fixes slow search queries (53 requests in alert analysis)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add index on name for LIKE queries
            $table->index('name', 'idx_users_name');

            // Add index on uuid for LIKE queries
            $table->index('uuid', 'idx_users_uuid');

            // Add index on special_id for LIKE queries
            $table->index('special_id', 'idx_users_special_id');

            // Composite index for family-related searches
            $table->index('family_id', 'idx_users_family_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_name');
            $table->dropIndex('idx_users_uuid');
            $table->dropIndex('idx_users_special_id');
            $table->dropIndex('idx_users_family_id');
        });
    }
};
