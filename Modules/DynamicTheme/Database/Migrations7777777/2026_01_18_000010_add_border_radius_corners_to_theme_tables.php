<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add border radius corners and missing border columns
     */
    public function up(): void
    {
        // Add to theme_children
        Schema::table('theme_children', function (Blueprint $table) {
            if (!Schema::hasColumn('theme_children', 'border_width')) {
                $table->integer('border_width')->default(0)->after('border_style');
            }
            if (!Schema::hasColumn('theme_children', 'border_color')) {
                $table->string('border_color')->nullable()->after('border_width');
            }
            if (!Schema::hasColumn('theme_children', 'border_radius_tl')) {
                $table->integer('border_radius_tl')->default(0)->after('border_color');
            }
            if (!Schema::hasColumn('theme_children', 'border_radius_tr')) {
                $table->integer('border_radius_tr')->default(0)->after('border_radius_tl');
            }
            if (!Schema::hasColumn('theme_children', 'border_radius_bl')) {
                $table->integer('border_radius_bl')->default(0)->after('border_radius_tr');
            }
            if (!Schema::hasColumn('theme_children', 'border_radius_br')) {
                $table->integer('border_radius_br')->default(0)->after('border_radius_bl');
            }
        });

        // Add to theme_assets
        Schema::table('theme_assets', function (Blueprint $table) {
            if (!Schema::hasColumn('theme_assets', 'border_width')) {
                $table->integer('border_width')->default(0)->after('z_index');
            }
            if (!Schema::hasColumn('theme_assets', 'border_style')) {
                $table->string('border_style')->nullable()->after('border_width');
            }
            if (!Schema::hasColumn('theme_assets', 'border_color')) {
                $table->string('border_color')->nullable()->after('border_style');
            }
            if (!Schema::hasColumn('theme_assets', 'border_radius_tl')) {
                $table->integer('border_radius_tl')->default(0)->after('border_color');
            }
            if (!Schema::hasColumn('theme_assets', 'border_radius_tr')) {
                $table->integer('border_radius_tr')->default(0)->after('border_radius_tl');
            }
            if (!Schema::hasColumn('theme_assets', 'border_radius_bl')) {
                $table->integer('border_radius_bl')->default(0)->after('border_radius_tr');
            }
            if (!Schema::hasColumn('theme_assets', 'border_radius_br')) {
                $table->integer('border_radius_br')->default(0)->after('border_radius_bl');
            }
        });

        // Add to config_theme_child_overrides
        Schema::table('config_theme_child_overrides', function (Blueprint $table) {
            if (!Schema::hasColumn('config_theme_child_overrides', 'background_color')) {
                $table->string('background_color')->nullable();
            }
            if (!Schema::hasColumn('config_theme_child_overrides', 'border_width')) {
                $table->integer('border_width')->nullable();
            }
            if (!Schema::hasColumn('config_theme_child_overrides', 'border_style')) {
                $table->string('border_style')->nullable();
            }
            if (!Schema::hasColumn('config_theme_child_overrides', 'border_color')) {
                $table->string('border_color')->nullable();
            }
            if (!Schema::hasColumn('config_theme_child_overrides', 'border_radius_tl')) {
                $table->integer('border_radius_tl')->nullable();
            }
            if (!Schema::hasColumn('config_theme_child_overrides', 'border_radius_tr')) {
                $table->integer('border_radius_tr')->nullable();
            }
            if (!Schema::hasColumn('config_theme_child_overrides', 'border_radius_bl')) {
                $table->integer('border_radius_bl')->nullable();
            }
            if (!Schema::hasColumn('config_theme_child_overrides', 'border_radius_br')) {
                $table->integer('border_radius_br')->nullable();
            }
        });

        // Add to config_child_asset_overrides
        Schema::table('config_child_asset_overrides', function (Blueprint $table) {
            if (!Schema::hasColumn('config_child_asset_overrides', 'border_width')) {
                $table->integer('border_width')->nullable();
            }
            if (!Schema::hasColumn('config_child_asset_overrides', 'border_style')) {
                $table->string('border_style')->nullable();
            }
            if (!Schema::hasColumn('config_child_asset_overrides', 'border_color')) {
                $table->string('border_color')->nullable();
            }
            if (!Schema::hasColumn('config_child_asset_overrides', 'border_radius_tl')) {
                $table->integer('border_radius_tl')->nullable();
            }
            if (!Schema::hasColumn('config_child_asset_overrides', 'border_radius_tr')) {
                $table->integer('border_radius_tr')->nullable();
            }
            if (!Schema::hasColumn('config_child_asset_overrides', 'border_radius_bl')) {
                $table->integer('border_radius_bl')->nullable();
            }
            if (!Schema::hasColumn('config_child_asset_overrides', 'border_radius_br')) {
                $table->integer('border_radius_br')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = ['border_width', 'border_color', 'border_radius_tl', 'border_radius_tr', 'border_radius_bl', 'border_radius_br'];
        
        Schema::table('theme_children', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });

        Schema::table('theme_assets', function (Blueprint $table) {
            $table->dropColumn(['border_width', 'border_style', 'border_color', 'border_radius_tl', 'border_radius_tr', 'border_radius_bl', 'border_radius_br']);
        });

        Schema::table('config_theme_child_overrides', function (Blueprint $table) {
            $table->dropColumn(['background_color', 'border_width', 'border_style', 'border_color', 'border_radius_tl', 'border_radius_tr', 'border_radius_bl', 'border_radius_br']);
        });

        Schema::table('config_child_asset_overrides', function (Blueprint $table) {
            $table->dropColumn(['border_width', 'border_style', 'border_color', 'border_radius_tl', 'border_radius_tr', 'border_radius_bl', 'border_radius_br']);
        });
    }
};
