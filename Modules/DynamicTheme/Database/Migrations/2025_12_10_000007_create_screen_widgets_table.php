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
        Schema::create('screen_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screen_id')->constrained('screens')->onDelete('cascade');
            $table->foreignId('widget_id')->constrained('widgets')->onDelete('cascade');
            $table->foreignId('theme_id')->nullable()->constrained('widget_themes')->onDelete('set null');
            $table->integer('order')->default(0)->comment('Display order (100+ for positioned widgets)');
            $table->boolean('is_positioned')->default(false)->comment('Is this a floating/positioned widget?');
            $table->json('position')->nullable()->comment('Position for floating widgets: {anchor, top, bottom, left, right}');
            $table->json('primary_settings')->nullable()->comment('Primary settings values (affects data)');
            $table->json('secondary_settings')->nullable()->comment('Secondary settings values (affects appearance)');
            $table->json('action')->nullable()->comment('Action configuration: {type, screen_key, url, etc.}');
            $table->string('min_app_version')->default('1.0.0');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('screen_id');
            $table->index('widget_id');
            $table->index('order');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screen_widgets');
    }
};
