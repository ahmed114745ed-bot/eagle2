<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add index on agency_id to optimize /api/agencies/details/{id} endpoint
     * Fixes slow queries when filtering users by agency (16 requests in alert analysis)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('agency_id', 'idx_users_agency_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_agency_id');
        });
    }
};
