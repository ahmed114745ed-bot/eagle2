<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->string('widget_type')->comment('Type of widget: banner, room, ranking, tab_bar, etc.');
            $table->string('widget_key')->unique()->comment('Unique key for the widget (e.g., live_banners, top_rooms)');
            $table->string('display_name')->comment('Display name shown in dashboard');
            $table->text('description')->nullable()->comment('Description of the widget');
            $table->boolean('is_repeatable')->default(false)->comment('Can this widget be added multiple times to a screen?');
            $table->boolean('has_children')->default(false)->comment('Does this widget have children (tabs, categories)?');
            $table->string('min_app_version')->default('1.0.0')->comment('Minimum app version required');
            $table->string('icon')->nullable()->comment('Icon for dashboard display');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('widget_type');
            $table->index('widget_key');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widgets');
    }
};
