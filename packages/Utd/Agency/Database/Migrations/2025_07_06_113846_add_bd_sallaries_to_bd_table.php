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
        if (! Schema::hasTable('bd_sallaries')) {
            return;
        }
        Schema::table('bd_sallaries', function (Blueprint $table) {
            // العمود يمكن أن يكون salary أو sallary حسب الجدول المُنشأ
            if (Schema::hasColumn('bd_sallaries', 'sallary')) {
                $table->decimal('sallary', 20, 4)->default(0)->change();
            } elseif (Schema::hasColumn('bd_sallaries', 'salary')) {
                $table->decimal('salary', 20, 4)->default(0)->change();
            }
            if (Schema::hasColumn('bd_sallaries', 'cut_amount')) {
                $table->decimal('cut_amount', 20, 4)->default(0)->change();
            }
            if (Schema::hasColumn('bd_sallaries', 'total_agency_sallary')) {
                $table->decimal('total_agency_sallary', 20, 4)->default(0)->change();
            }
            if (Schema::hasColumn('bd_sallaries', 'total_users_sallary')) {
                $table->decimal('total_users_sallary', 20, 4)->default(0)->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bd_sallaries', function (Blueprint $table) {
            $table->float('sallary')->default(0)->change();
            $table->float('cut_amount')->default(0)->change();
            $table->float('total_agency_sallary')->default(0)->change();
            $table->float('total_users_sallary')->default(0)->change();

        });
    }
};
