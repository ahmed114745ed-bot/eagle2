<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add designer position/size columns to config override tables
     */
    public function up(): void
    {
        // Add to config_theme_child_overrides
        if (Schema::hasTable('config_theme_child_overrides')) {
            Schema::table('config_theme_child_overrides', function (Blueprint $table) {
                if (!Schema::hasColumn('config_theme_child_overrides', 'width')) {
                    $table->integer('width')->nullable()->after('position');
                }
                if (!Schema::hasColumn('config_theme_child_overrides', 'height')) {
                    $table->integer('height')->nullable()->after('width');
                }
                if (!Schema::hasColumn('config_theme_child_overrides', 'x')) {
                    $table->integer('x')->nullable()->after('height');
                }
                if (!Schema::hasColumn('config_theme_child_overrides', 'y')) {
                    $table->integer('y')->nullable()->after('x');
                }
                if (!Schema::hasColumn('config_theme_child_overrides', 'rotation')) {
                    $table->float('rotation')->nullable()->after('y');
                }
                if (!Schema::hasColumn('config_theme_child_overrides', 'scale')) {
                    $table->float('scale')->nullable()->after('rotation');
                }
                if (!Schema::hasColumn('config_theme_child_overrides', 'opacity')) {
                    $table->float('opacity')->nullable()->after('scale');
                }
                if (!Schema::hasColumn('config_theme_child_overrides', 'z_index')) {
                    $table->integer('z_index')->nullable()->after('opacity');
                }
            });
        }

        // Add to config_child_asset_overrides
        if (Schema::hasTable('config_child_asset_overrides')) {
            Schema::table('config_child_asset_overrides', function (Blueprint $table) {
                if (!Schema::hasColumn('config_child_asset_overrides', 'width')) {
                    $table->integer('width')->nullable()->after('file_path');
                }
                if (!Schema::hasColumn('config_child_asset_overrides', 'height')) {
                    $table->integer('height')->nullable()->after('width');
                }
                if (!Schema::hasColumn('config_child_asset_overrides', 'x')) {
                    $table->integer('x')->nullable()->after('height');
                }
                if (!Schema::hasColumn('config_child_asset_overrides', 'y')) {
                    $table->integer('y')->nullable()->after('x');
                }
                if (!Schema::hasColumn('config_child_asset_overrides', 'rotation')) {
                    $table->float('rotation')->nullable()->after('y');
                }
                if (!Schema::hasColumn('config_child_asset_overrides', 'scale')) {
                    $table->float('scale')->nullable()->after('rotation');
                }
                if (!Schema::hasColumn('config_child_asset_overrides', 'opacity')) {
                    $table->float('opacity')->nullable()->after('scale');
                }
                if (!Schema::hasColumn('config_child_asset_overrides', 'z_index')) {
                    $table->integer('z_index')->nullable()->after('opacity');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columnsToRemove = ['width', 'height', 'x', 'y', 'rotation', 'scale', 'opacity', 'z_index'];
        
        if (Schema::hasTable('config_theme_child_overrides')) {
            Schema::table('config_theme_child_overrides', function (Blueprint $table) use ($columnsToRemove) {
                foreach ($columnsToRemove as $col) {
                    if (Schema::hasColumn('config_theme_child_overrides', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('config_child_asset_overrides')) {
            Schema::table('config_child_asset_overrides', function (Blueprint $table) use ($columnsToRemove) {
                foreach ($columnsToRemove as $col) {
                    if (Schema::hasColumn('config_child_asset_overrides', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
