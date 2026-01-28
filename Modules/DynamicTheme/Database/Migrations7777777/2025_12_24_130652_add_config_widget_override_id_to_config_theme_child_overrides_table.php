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
        Schema::table('config_theme_child_overrides', function (Blueprint $table) {
            $table->unsignedBigInteger('config_widget_override_id')
                  ->after('configuration_id');

          
        });
    }

    public function down(): void
    {
        Schema::table('config_theme_child_overrides', function (Blueprint $table) {
            $table->dropColumn('config_widget_override_id');
        });
    }
};
