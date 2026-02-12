<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (! Schema::hasTable('bd_agency_host_sallaries')) {
            return;
        }
        Schema::table('bd_agency_host_sallaries', function (Blueprint $table) {
            if (Schema::hasColumn('bd_agency_host_sallaries', 'agency_sallary')) {
                $table->decimal('agency_sallary', 20, 4)->change();
            }
            if (Schema::hasColumn('bd_agency_host_sallaries', 'user_sallary')) {
                $table->decimal('user_sallary', 20, 4)->change();
            }
            if (Schema::hasColumn('bd_agency_host_sallaries', 'amount')) {
                $table->decimal('amount', 20, 4)->change();
            }
            if (Schema::hasColumn('bd_agency_host_sallaries', 'salary')) {
                $table->decimal('salary', 20, 4)->change();
            }
        });
    }

    public function down()
    {
        Schema::table('bd_agency_host_sallaries', function (Blueprint $table) {
            $table->decimal('agency_sallary', 12, 2)->change();
            $table->decimal('user_sallary', 12, 2)->change();
            $table->decimal('amount', 12, 2)->change();
        });
    }
};
