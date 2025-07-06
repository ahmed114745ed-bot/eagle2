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
        Schema::table('bd_sallaries', function (Blueprint $table) {
            $table->decimal('sallary', 20, 4)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('bd_sallaries', function (Blueprint $table) {
            $table->float('sallary')->default(0)->change();
        });
    }
};
