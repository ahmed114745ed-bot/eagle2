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
        Schema::table('widget_themes', function (Blueprint $table) {
            $table->unsignedInteger('widget_width')->nullable()->after('is_active')->comment('Designer widget width in pixels');
            $table->unsignedInteger('widget_height')->nullable()->after('widget_width')->comment('Designer widget height in pixels');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('widget_themes', function (Blueprint $table) {
            $table->dropColumn(['widget_width', 'widget_height']);
        });
    }
};
