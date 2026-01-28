<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add widget layout/position columns to config_widget_overrides for Visual Designer
     */
    public function up(): void
    {
        if (Schema::hasTable('config_widget_overrides')) {
            Schema::table('config_widget_overrides', function (Blueprint $table) {
                // Position
                if (!Schema::hasColumn('config_widget_overrides', 'x')) {
                    $table->integer('x')->nullable()->default(0);
                }
                if (!Schema::hasColumn('config_widget_overrides', 'y')) {
                    $table->integer('y')->nullable()->default(0);
                }
                // Dimensions
                if (!Schema::hasColumn('config_widget_overrides', 'width')) {
                    $table->integer('width')->nullable()->default(350);
                }
                if (!Schema::hasColumn('config_widget_overrides', 'height')) {
                    $table->integer('height')->nullable()->default(150);
                }
                // Z-Index & Opacity
                if (!Schema::hasColumn('config_widget_overrides', 'z_index')) {
                    $table->integer('z_index')->nullable()->default(0);
                }
                if (!Schema::hasColumn('config_widget_overrides', 'opacity')) {
                    $table->float('opacity')->nullable()->default(1);
                }
                // Layout mode settings
                if (!Schema::hasColumn('config_widget_overrides', 'layout_mode')) {
                    $table->string('layout_mode', 20)->nullable()->default('absolute');
                }
                if (!Schema::hasColumn('config_widget_overrides', 'layout_gap')) {
                    $table->integer('layout_gap')->nullable()->default(8);
                }
                if (!Schema::hasColumn('config_widget_overrides', 'layout_padding')) {
                    $table->integer('layout_padding')->nullable()->default(8);
                }
                if (!Schema::hasColumn('config_widget_overrides', 'child_width')) {
                    $table->integer('child_width')->nullable()->default(80);
                }
                if (!Schema::hasColumn('config_widget_overrides', 'child_height')) {
                    $table->integer('child_height')->nullable()->default(100);
                }
                // Infinite scroll / Slider settings
                if (!Schema::hasColumn('config_widget_overrides', 'infinite_scroll')) {
                    $table->boolean('infinite_scroll')->nullable()->default(false);
                }
                if (!Schema::hasColumn('config_widget_overrides', 'scroll_speed')) {
                    $table->integer('scroll_speed')->nullable()->default(3);
                }
                // Style
                if (!Schema::hasColumn('config_widget_overrides', 'background_color')) {
                    $table->string('background_color', 50)->nullable()->default('transparent');
                }
                if (!Schema::hasColumn('config_widget_overrides', 'border_radius')) {
                    $table->integer('border_radius')->nullable()->default(8);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columnsToRemove = [
            'x', 'y', 'width', 'height', 'z_index', 'opacity',
            'layout_mode', 'layout_gap', 'layout_padding', 
            'child_width', 'child_height',
            'infinite_scroll', 'scroll_speed',
            'background_color', 'border_radius'
        ];
        
        if (Schema::hasTable('config_widget_overrides')) {
            Schema::table('config_widget_overrides', function (Blueprint $table) use ($columnsToRemove) {
                foreach ($columnsToRemove as $col) {
                    if (Schema::hasColumn('config_widget_overrides', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
