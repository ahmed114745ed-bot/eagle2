<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add border and style columns to config_theme_child_overrides
     */
    public function up(): void
    {
        if (Schema::hasTable('config_theme_child_overrides')) {
            Schema::table('config_theme_child_overrides', function (Blueprint $table) {
                if (!Schema::hasColumn('config_theme_child_overrides', 'background_color')) {
                    $table->string('background_color', 50)->nullable()->after('z_index');
                }
                if (!Schema::hasColumn('config_theme_child_overrides', 'border_width')) {
                    $table->integer('border_width')->nullable()->default(0)->after('background_color');
                }
                if (!Schema::hasColumn('config_theme_child_overrides', 'border_style')) {
                    $table->string('border_style', 20)->nullable()->after('border_width');
                }
                if (!Schema::hasColumn('config_theme_child_overrides', 'border_color')) {
                    $table->string('border_color', 50)->nullable()->after('border_style');
                }
                if (!Schema::hasColumn('config_theme_child_overrides', 'border_radius_tl')) {
                    $table->integer('border_radius_tl')->nullable()->default(0)->after('border_color');
                }
                if (!Schema::hasColumn('config_theme_child_overrides', 'border_radius_tr')) {
                    $table->integer('border_radius_tr')->nullable()->default(0)->after('border_radius_tl');
                }
                if (!Schema::hasColumn('config_theme_child_overrides', 'border_radius_bl')) {
                    $table->integer('border_radius_bl')->nullable()->default(0)->after('border_radius_tr');
                }
                if (!Schema::hasColumn('config_theme_child_overrides', 'border_radius_br')) {
                    $table->integer('border_radius_br')->nullable()->default(0)->after('border_radius_bl');
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
            'background_color', 'border_width', 'border_style', 'border_color',
            'border_radius_tl', 'border_radius_tr', 'border_radius_bl', 'border_radius_br'
        ];

        if (Schema::hasTable('config_theme_child_overrides')) {
            Schema::table('config_theme_child_overrides', function (Blueprint $table) use ($columnsToRemove) {
                foreach ($columnsToRemove as $col) {
                    if (Schema::hasColumn('config_theme_child_overrides', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
