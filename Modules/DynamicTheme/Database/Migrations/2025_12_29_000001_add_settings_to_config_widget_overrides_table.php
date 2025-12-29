<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('config_widget_overrides', function (Blueprint $table) {
            $table->json('primary_settings')->nullable()->after('selected_theme_id');
            $table->json('secondary_settings')->nullable()->after('primary_settings');
            $table->json('action')->nullable()->after('secondary_settings');
            if (!Schema::hasColumn('config_widget_overrides', 'selected_child_theme_id')) {
                $table->unsignedBigInteger('selected_child_theme_id')->nullable()->after('selected_theme_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('config_widget_overrides', function (Blueprint $table) {
            $table->dropColumn(['primary_settings', 'secondary_settings', 'action']);
            if (Schema::hasColumn('config_widget_overrides', 'selected_child_theme_id')) {
                $table->dropColumn('selected_child_theme_id');
            }
        });
    }
};
