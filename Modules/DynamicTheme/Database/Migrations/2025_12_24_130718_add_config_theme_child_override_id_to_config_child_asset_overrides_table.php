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
        Schema::table('config_child_asset_overrides', function (Blueprint $table) {
            $table->unsignedBigInteger('config_theme_child_override_id')
                  ->after('configuration_id');

         
        });
    }

    public function down(): void
    {
        Schema::table('config_child_asset_overrides', function (Blueprint $table) {
            $table->dropColumn('config_theme_child_override_id');
        });
    }
};
