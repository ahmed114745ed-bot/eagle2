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
        // Rename job_type to type if exists
        if (Schema::hasTable('agency_user_jobs') && Schema::hasColumn('agency_user_jobs', 'job_type')) {
            Schema::table('agency_user_jobs', function (Blueprint $table) {
                $table->renameColumn('job_type', 'type');
            });
        }
        
        // Add type column if not exists
        if (Schema::hasTable('agency_user_jobs') && !Schema::hasColumn('agency_user_jobs', 'type')) {
            Schema::table('agency_user_jobs', function (Blueprint $table) {
                $table->string('type', 100)->nullable()->comment('owner, admin, requestManger, operator')->after('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('agency_user_jobs') && Schema::hasColumn('agency_user_jobs', 'type')) {
            Schema::table('agency_user_jobs', function (Blueprint $table) {
                $table->renameColumn('type', 'job_type');
            });
        }
    }
};
