<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add is_background and object_fit columns to config_child_asset_overrides
     */
    public function up(): void
    {
        if (Schema::hasTable('config_child_asset_overrides')) {
            Schema::table('config_child_asset_overrides', function (Blueprint $table) {
                if (!Schema::hasColumn('config_child_asset_overrides', 'is_background')) {
                    $table->boolean('is_background')->nullable()->default(false)->after('is_visible');
                }
                if (!Schema::hasColumn('config_child_asset_overrides', 'object_fit')) {
                    $table->string('object_fit', 20)->nullable()->default('contain')->after('is_background');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('config_child_asset_overrides')) {
            Schema::table('config_child_asset_overrides', function (Blueprint $table) {
                if (Schema::hasColumn('config_child_asset_overrides', 'is_background')) {
                    $table->dropColumn('is_background');
                }
                if (Schema::hasColumn('config_child_asset_overrides', 'object_fit')) {
                    $table->dropColumn('object_fit');
                }
            });
        }
    }
};
