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
        Schema::create('screens', function (Blueprint $table) {
            $table->id();
            $table->string('screen_key')->unique()->comment('Unique identifier for the screen (e.g., home_hot, profile)');
            $table->string('screen_name')->comment('Display name for the screen');
            $table->string('min_app_version')->default('1.0.0')->comment('Minimum app version required');
            $table->json('layout')->nullable()->comment('Layout settings: direction, background_color, background_asset');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('screen_key');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screens');
    }
};
