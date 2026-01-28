<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add position and size columns to theme_assets for visual designer
     */
    public function up(): void
    {
        Schema::table('theme_assets', function (Blueprint $table) {
            $table->integer('width')->nullable()->after('order');
            $table->integer('height')->nullable()->after('width');
            $table->integer('x')->default(0)->after('height');
            $table->integer('y')->default(0)->after('x');
            $table->float('rotation')->default(0)->after('y');
            $table->float('scale')->default(1)->after('rotation');
            $table->float('opacity')->default(1)->after('scale');
            $table->integer('z_index')->default(0)->after('opacity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('theme_assets', function (Blueprint $table) {
            $table->dropColumn(['width', 'height', 'x', 'y', 'rotation', 'scale', 'opacity', 'z_index']);
        });
    }
};
