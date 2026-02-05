<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * تحويل أعمدة add_month و add_year إلى month و year
     * Converting add_month and add_year columns to month and year
     */
    public function up(): void
    {
        if (!Schema::hasTable('bd_agency_host_sallaries')) {
            return;
        }

        if (Schema::hasColumn('bd_agency_host_sallaries', 'add_month') 
            && !Schema::hasColumn('bd_agency_host_sallaries', 'month')) {
            Schema::table('bd_agency_host_sallaries', function (Blueprint $table) {
                $table->renameColumn('add_month', 'month');
            });
        }
        
        if (Schema::hasColumn('bd_agency_host_sallaries', 'add_year') 
            && !Schema::hasColumn('bd_agency_host_sallaries', 'year')) {
            Schema::table('bd_agency_host_sallaries', function (Blueprint $table) {
                $table->renameColumn('add_year', 'year');
            });
        }
    }

    /**
     * Reverse the migrations.
     * 
     * No rollback to protect data integrity
     */
    public function down(): void
    {
        // Do not perform any rollback operation to protect existing data
        return;
    }
};
