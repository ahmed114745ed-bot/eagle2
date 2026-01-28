<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add position and size columns to theme_children for visual designer
     */
    public function up(): void
    {
        Schema::table('theme_children', function (Blueprint $table) {
            $table->integer('width')->nullable()->after('position');
            $table->integer('height')->nullable()->after('width');
            $table->integer('x')->default(0)->after('height');
            $table->integer('y')->default(0)->after('x');
            $table->float('rotation')->default(0)->after('y');
            $table->float('scale')->default(1)->after('rotation');
            $table->float('opacity')->default(1)->after('scale');
            $table->integer('z_index')->default(0)->after('opacity');
            $table->string('background_color')->nullable()->after('z_index');
            $table->string('border_style')->nullable()->after('background_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('theme_children', function (Blueprint $table) {
            $table->dropColumn(['width', 'height', 'x', 'y', 'rotation', 'scale', 'opacity', 'z_index', 'background_color', 'border_style']);
        });
    }
};
