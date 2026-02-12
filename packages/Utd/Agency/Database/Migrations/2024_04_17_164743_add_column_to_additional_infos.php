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
        if (! Schema::hasTable('additional_infos') || ! Schema::hasColumn('additional_infos', 'country')) {
            return;
        }
        Schema::table('additional_infos', function (Blueprint $table) {
            $table->string('country')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('additional_infos', function (Blueprint $table) {
            //
        });
    }
};
