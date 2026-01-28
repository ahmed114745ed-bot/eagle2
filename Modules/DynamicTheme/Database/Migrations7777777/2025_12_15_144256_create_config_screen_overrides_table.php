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
        Schema::create('config_screen_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('configuration_id')->constrained('client_configurations')->onDelete('cascade');
            $table->foreignId('screen_id')->constrained('screens')->onDelete('cascade');
            $table->boolean('is_visible')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->unique(['configuration_id', 'screen_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_screen_overrides');
    }
};
