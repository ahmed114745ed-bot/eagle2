<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add auto_scroll column to config_widget_overrides for Visual Designer
     */
    public function up(): void
    {
        if (Schema::hasTable('config_widget_overrides')) {
            Schema::table('config_widget_overrides', function (Blueprint $table) {
                if (!Schema::hasColumn('config_widget_overrides', 'auto_scroll')) {
                    $table->boolean('auto_scroll')->nullable()->default(false)->after('infinite_scroll');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('config_widget_overrides')) {
            Schema::table('config_widget_overrides', function (Blueprint $table) {
                if (Schema::hasColumn('config_widget_overrides', 'auto_scroll')) {
                    $table->dropColumn('auto_scroll');
                }
            });
        }
    }
};
