<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('config_theme_asset_overrides', function (Blueprint $table) {
            $table->id();

            $table->foreignId('configuration_id')
                ->constrained('client_configurations')
                ->cascadeOnDelete();

            $table->foreignId('theme_asset_id')
                ->constrained('theme_assets')
                ->cascadeOnDelete();

            $table->string('override_url')->nullable();
            $table->string('file_path')->nullable();
            $table->string('original_filename')->nullable();

            $table->timestamps();

            $table->unique(
                ['configuration_id', 'theme_asset_id'],
                'config_theme_asset_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('config_theme_asset_overrides');
    }
};
