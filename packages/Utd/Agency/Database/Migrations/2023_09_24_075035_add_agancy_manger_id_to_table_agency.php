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
        if (!Schema::hasTable('agencies') || Schema::hasColumn('agencies', 'agency_manger_id')) return;
        Schema::table('agencies', function (Blueprint $table) {
            $table->integer('agency_manger_id')->unsigned()->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropColumn('agency_manger_id');
        });
    }
};
