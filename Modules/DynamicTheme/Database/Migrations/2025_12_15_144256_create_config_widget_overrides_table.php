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
        Schema::create('config_widget_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('configuration_id')->constrained('client_configurations')->onDelete('cascade');
            $table->foreignId('screen_id')->constrained('screens')->onDelete('cascade');
            $table->foreignId('screen_widget_id')->constrained('screen_widgets')->onDelete('cascade');
            $table->boolean('is_visible')->default(true);
            $table->integer('display_order')->default(0);
            $table->foreignId('selected_theme_id')->nullable()->constrained('widget_themes')->onDelete('set null');
            $table->timestamps();

            $table->unique(['configuration_id', 'screen_widget_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_widget_overrides');
    }
};
