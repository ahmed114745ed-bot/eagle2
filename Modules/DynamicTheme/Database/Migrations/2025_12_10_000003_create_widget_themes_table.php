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
        Schema::create('widget_themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('widget_id')->constrained('widgets')->onDelete('cascade');
            $table->string('theme_key')->unique()->comment('Unique key for the theme (e.g., room_grid_golden)');
            $table->string('theme_name')->comment('Display name for the theme');
            $table->string('preview_image')->nullable()->comment('Preview image URL for dashboard');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false)->comment('Is this the default theme for the widget?');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('widget_id');
            $table->index('theme_key');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widget_themes');
    }
};
