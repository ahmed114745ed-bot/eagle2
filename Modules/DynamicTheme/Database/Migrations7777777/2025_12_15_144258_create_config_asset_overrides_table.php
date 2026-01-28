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
        Schema::create('config_asset_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('configuration_id')->constrained('client_configurations')->onDelete('cascade');
            $table->foreignId('theme_asset_id')->constrained('theme_assets')->onDelete('cascade');
            $table->string('file_path')->nullable()->comment('Override file path in storage');
            $table->string('original_filename')->nullable();
            $table->text('value_override')->nullable()->comment('Override value if not a file');
            $table->timestamps();

            $table->unique(['configuration_id', 'theme_asset_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_asset_overrides');
    }
};
