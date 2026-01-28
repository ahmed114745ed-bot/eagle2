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
        if (!Schema::hasTable('additional_infos') || !Schema::hasColumn('additional_infos', 'country_id') || Schema::hasColumn('additional_infos', 'country')) return;
        Schema::table('additional_infos', function (Blueprint $table) {
            $table->string('country_id')->change();
            $table->renameColumn('country_id', 'country');
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
