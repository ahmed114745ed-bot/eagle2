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
        Schema::table('config_widget_overrides', function (Blueprint $table) {
            $table->unsignedBigInteger('widget_id')
                  ->after('screen_widget_id');

         
        });
    }

    public function down(): void
    {
        Schema::table('config_widget_overrides', function (Blueprint $table) {
            $table->dropColumn('widget_id');
        });
    }
};
