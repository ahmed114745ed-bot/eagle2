<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add text styling columns to config_child_asset_overrides
     */
    public function up(): void
    {
        if (Schema::hasTable('config_child_asset_overrides')) {
            Schema::table('config_child_asset_overrides', function (Blueprint $table) {
                // Text content
                if (!Schema::hasColumn('config_child_asset_overrides', 'text_content')) {
                    $table->text('text_content')->nullable()->after('text');
                }
                // Text color
                if (!Schema::hasColumn('config_child_asset_overrides', 'text_color')) {
                    $table->string('text_color', 20)->nullable()->default('#ffffff')->after('text_content');
                }
                // Font size
                if (!Schema::hasColumn('config_child_asset_overrides', 'font_size')) {
                    $table->integer('font_size')->nullable()->default(14)->after('text_color');
                }
                // Font weight
                if (!Schema::hasColumn('config_child_asset_overrides', 'font_weight')) {
                    $table->string('font_weight', 20)->nullable()->default('normal')->after('font_size');
                }
                // Font family
                if (!Schema::hasColumn('config_child_asset_overrides', 'font_family')) {
                    $table->string('font_family', 100)->nullable()->default('inherit')->after('font_weight');
                }
                // Text align
                if (!Schema::hasColumn('config_child_asset_overrides', 'text_align')) {
                    $table->string('text_align', 20)->nullable()->default('center')->after('font_family');
                }
                // Line height
                if (!Schema::hasColumn('config_child_asset_overrides', 'line_height')) {
                    $table->decimal('line_height', 3, 1)->nullable()->default(1.4)->after('text_align');
                }
                // Letter spacing
                if (!Schema::hasColumn('config_child_asset_overrides', 'letter_spacing')) {
                    $table->decimal('letter_spacing', 5, 1)->nullable()->default(0)->after('line_height');
                }
                // Text shadow
                if (!Schema::hasColumn('config_child_asset_overrides', 'text_shadow')) {
                    $table->string('text_shadow', 100)->nullable()->default('none')->after('letter_spacing');
                }
                // Text decoration
                if (!Schema::hasColumn('config_child_asset_overrides', 'text_decoration')) {
                    $table->string('text_decoration', 30)->nullable()->default('none')->after('text_shadow');
                }
                // Text transform
                if (!Schema::hasColumn('config_child_asset_overrides', 'text_transform')) {
                    $table->string('text_transform', 20)->nullable()->default('none')->after('text_decoration');
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
                $columns = [
                    'text_content', 'text_color', 'font_size', 'font_weight', 'font_family',
                    'text_align', 'line_height', 'letter_spacing', 'text_shadow',
                    'text_decoration', 'text_transform'
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('config_child_asset_overrides', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
