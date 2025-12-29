<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('config_theme_child_overrides', function (Blueprint $table) {
            $table->id();

            $table->foreignId('configuration_id')
                ->constrained('client_configurations')
                ->cascadeOnDelete();

            $table->foreignId('theme_child_id')
                ->constrained('theme_children')
                ->cascadeOnDelete();

            $table->boolean('is_visible')->default(true);
            $table->integer('order')->default(0)->nullable();
            $table->string('action')->nullable();
            $table->string('position')->nullable();

            $table->timestamps();

            $table->unique(
                ['configuration_id', 'theme_child_id'],
                'config_theme_child_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('config_theme_child_overrides');
    }
};
